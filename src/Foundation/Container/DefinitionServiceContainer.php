<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Sif\Foundation\Contracts\ScopedServiceContainerInterface;
use Sif\Foundation\Contracts\ServiceContainerInterface;
use Sif\Foundation\Contracts\ServiceDefinitionRegistryInterface;
use Sif\Foundation\Contracts\TaggedServiceLocatorInterface;
use Sif\Foundation\Contracts\ServiceScopeInterface;
use Sif\Foundation\Exceptions\CircularServiceDependencyException;
use Sif\Foundation\Exceptions\MissingActiveServiceScopeException;
use Sif\Foundation\Exceptions\ServiceCreationException;
use Sif\Foundation\Exceptions\ServiceDefinitionNotFoundException;
use Sif\Foundation\Exceptions\UnresolvableConstructorParameterException;
use Sif\Foundation\Exceptions\UnresolvableServiceException;
use Throwable;

final class DefinitionServiceContainer implements
    ScopedServiceContainerInterface,
    TaggedServiceLocatorInterface
{
    /**
     * @var array<string, object>
     */
    private array $singletons = [];

    /**
     * @var list<ServiceIdentifier>
     */
    private array $activePath = [];

    public function __construct(
        private readonly ServiceDefinitionRegistryInterface $definitions,
        private readonly ContextualBindingRegistry $contextualBindings = new ContextualBindingRegistry(),
    ) {
    }

    public function has(ServiceIdentifier $identifier): bool
    {
        return $this->definitions->has($identifier);
    }

    public function get(ServiceIdentifier $identifier): object
    {
        return $this->resolve(
            identifier: $identifier,
            scopeState: null,
            resolvingContainer: $this,
        );
    }

    public function lazy(
        ServiceIdentifier $identifier,
    ): \Sif\Foundation\Contracts\LazyServiceReferenceInterface {
        return new LazyServiceReference($this, $identifier);
    }

    public function beginScope(
        ScopeIdentifier $identifier,
    ): ServiceScopeInterface {
        return new ServiceScope(
            container: $this,
            state: new ServiceScopeState($identifier),
        );
    }

    public function resolutionPath(): ResolutionPath
    {
        return new ResolutionPath($this->activePath);
    }

    public function forget(ServiceIdentifier $identifier): void
    {
        $terminal = $this->terminalIdentifier($identifier);

        unset($this->singletons[$terminal->value()]);
    }

    public function clearSingletons(): void
    {
        $this->singletons = [];
    }

    /**
     * @return list<TaggedService>
     */
    public function tagged(string $tag): array
    {
        return $this->definitions->tagged($tag);
    }

    /**
     * @return list<object>
     */
    public function resolveTagged(string $tag): array
    {
        return array_map(
            fn (TaggedService $service): object => $this->get(
                $service->identifier(),
            ),
            $this->tagged($tag),
        );
    }

    public function getWithinScope(
        ServiceIdentifier $identifier,
        ServiceScopeState $state,
        ServiceContainerInterface $scopedContainer,
    ): object {
        $state->assertOpen();

        return $this->resolve(
            identifier: $identifier,
            scopeState: $state,
            resolvingContainer: $scopedContainer,
        );
    }

    public function forgetWithinScope(
        ServiceIdentifier $identifier,
        ServiceScopeState $state,
    ): void {
        $state->assertOpen();

        $terminal = $this->terminalIdentifier($identifier);
        $definition = $this->definitions->get($terminal);

        if ($definition->lifetime() === ServiceLifetime::Scoped) {
            $state->forget($terminal);

            return;
        }

        $this->forget($terminal);
    }

    private function resolve(
        ServiceIdentifier $identifier,
        ?ServiceScopeState $scopeState,
        ServiceContainerInterface $resolvingContainer,
    ): object {
        $scopeState?->assertOpen();

        $this->assertNotCircular($identifier);
        $this->activePath[] = $identifier;

        try {
            $definition = $this->definitions->get($identifier);

            if ($definition->isAlias()) {
                $target = $definition->aliasTarget();

                if ($target === null) {
                    throw new UnresolvableServiceException(
                        requestedIdentifier: $identifier,
                        path: $this->resolutionPath(),
                        message: sprintf(
                            'Alias "%s" has no target.',
                            $identifier->value(),
                        ),
                    );
                }

                return $this->resolve(
                    identifier: $target,
                    scopeState: $scopeState,
                    resolvingContainer: $resolvingContainer,
                );
            }

            $lifetime = $definition->lifetime();

            if ($lifetime === ServiceLifetime::Scoped) {
                if ($scopeState === null) {
                    throw new MissingActiveServiceScopeException(
                        requestedIdentifier: $identifier,
                        path: $this->resolutionPath(),
                    );
                }

                if ($scopeState->has($identifier)) {
                    return $scopeState->get($identifier);
                }

                $service = $this->create(
                    definition: $definition,
                    scopeState: $scopeState,
                    resolvingContainer: $resolvingContainer,
                );

                $scopeState->put($identifier, $service);

                return $service;
            }

            if (
                $lifetime === ServiceLifetime::Singleton
                && isset($this->singletons[$identifier->value()])
            ) {
                return $this->singletons[$identifier->value()];
            }

            $service = $this->create(
                definition: $definition,
                scopeState: $scopeState,
                resolvingContainer: $resolvingContainer,
            );

            if ($lifetime === ServiceLifetime::Singleton) {
                $this->singletons[$identifier->value()] = $service;
            }

            return $service;
        } finally {
            array_pop($this->activePath);
        }
    }

    private function create(
        ServiceDefinition $definition,
        ?ServiceScopeState $scopeState,
        ServiceContainerInterface $resolvingContainer,
    ): object {
        try {
            return match ($definition->kind()) {
                ServiceDefinitionKind::Instance => $this->resolveInstance(
                    $definition,
                ),
                ServiceDefinitionKind::Factory => $this->resolveFactory(
                    definition: $definition,
                    resolvingContainer: $resolvingContainer,
                ),
                ServiceDefinitionKind::ClassType => $this->resolveClass(
                    definition: $definition,
                    scopeState: $scopeState,
                    resolvingContainer: $resolvingContainer,
                ),
                ServiceDefinitionKind::Alias => throw new UnresolvableServiceException(
                    requestedIdentifier: $definition->identifier(),
                    path: $this->resolutionPath(),
                    message: 'Alias definitions must be resolved before creation.',
                ),
            };
        } catch (
            CircularServiceDependencyException
            | MissingActiveServiceScopeException
            | UnresolvableServiceException
            | ServiceDefinitionNotFoundException $failure
        ) {
            throw $failure;
        } catch (Throwable $failure) {
            throw new ServiceCreationException(
                requestedIdentifier: $definition->identifier(),
                path: $this->resolutionPath(),
                cause: $failure,
            );
        }
    }

    private function resolveInstance(
        ServiceDefinition $definition,
    ): object {
        $instance = $definition->instance();

        if ($instance === null) {
            throw new UnresolvableServiceException(
                requestedIdentifier: $definition->identifier(),
                path: $this->resolutionPath(),
                message: sprintf(
                    'Instance definition "%s" contains no instance.',
                    $definition->identifier()->value(),
                ),
            );
        }

        return $instance;
    }

    private function resolveFactory(
        ServiceDefinition $definition,
        ServiceContainerInterface $resolvingContainer,
    ): object {
        $factory = $definition->factory();

        if ($factory === null) {
            throw new UnresolvableServiceException(
                requestedIdentifier: $definition->identifier(),
                path: $this->resolutionPath(),
                message: sprintf(
                    'Factory definition "%s" contains no factory.',
                    $definition->identifier()->value(),
                ),
            );
        }

        return $factory($resolvingContainer);
    }

    private function resolveClass(
        ServiceDefinition $definition,
        ?ServiceScopeState $scopeState,
        ServiceContainerInterface $resolvingContainer,
    ): object {
        $className = $definition->className();

        if ($className === null) {
            throw new UnresolvableServiceException(
                requestedIdentifier: $definition->identifier(),
                path: $this->resolutionPath(),
                message: sprintf(
                    'Class definition "%s" contains no class name.',
                    $definition->identifier()->value(),
                ),
            );
        }

        $reflection = new ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            throw new UnresolvableServiceException(
                requestedIdentifier: $definition->identifier(),
                path: $this->resolutionPath(),
                message: sprintf(
                    'Service class "%s" is not instantiable.',
                    $className,
                ),
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        if (!$definition->autowire()) {
            if ($constructor->getNumberOfRequiredParameters() > 0) {
                throw new UnresolvableServiceException(
                    requestedIdentifier: $definition->identifier(),
                    path: $this->resolutionPath(),
                    message: sprintf(
                        'Service class "%s" requires constructor arguments and autowiring is disabled.',
                        $className,
                    ),
                );
            }

            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $this->resolveParameter(
                definition: $definition,
                parameter: $parameter,
                scopeState: $scopeState,
                resolvingContainer: $resolvingContainer,
            );
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private function resolveParameter(
        ServiceDefinition $definition,
        ReflectionParameter $parameter,
        ?ServiceScopeState $scopeState,
        ServiceContainerInterface $resolvingContainer,
    ): mixed {
        $bindings = $definition->constructorBindings();
        $name = $parameter->getName();

        if ($bindings->has($name)) {
            return $this->resolveBinding(
                binding: $bindings->get($name),
                resolvingContainer: $resolvingContainer,
            );
        }

        if (
            $this->contextualBindings->has(
                $definition->identifier(),
                $name,
            )
        ) {
            return $this->resolveBinding(
                binding: $this->contextualBindings
                    ->get($definition->identifier(), $name)
                    ->binding(),
                resolvingContainer: $resolvingContainer,
            );
        }

        $type = $parameter->getType();

        if ($type instanceof ReflectionUnionType) {
            throw $this->parameterFailure(
                $definition,
                $parameter,
                'Union-typed constructor parameters require an explicit binding.',
            );
        }

        if ($type instanceof ReflectionIntersectionType) {
            throw $this->parameterFailure(
                $definition,
                $parameter,
                'Intersection-typed constructor parameters require an explicit binding.',
            );
        }

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $identifier = new ServiceIdentifier($type->getName());

            if ($this->has($identifier)) {
                return $this->resolve(
                    identifier: $identifier,
                    scopeState: $scopeState,
                    resolvingContainer: $resolvingContainer,
                );
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        if (
            $type instanceof ReflectionNamedType
            && $type->isBuiltin()
        ) {
            throw $this->parameterFailure(
                $definition,
                $parameter,
                sprintf(
                    'Builtin constructor parameter "%s" requires an explicit binding.',
                    $name,
                ),
            );
        }

        throw $this->parameterFailure(
            $definition,
            $parameter,
            sprintf(
                'Constructor parameter "%s" cannot be resolved.',
                $name,
            ),
        );
    }

    private function resolveBinding(
        ConstructorArgumentBinding $binding,
        ServiceContainerInterface $resolvingContainer,
    ): mixed {
        if ($binding->kind() === ConstructorBindingKind::Value) {
            return $binding->boundValue();
        }

        $identifier = $binding->serviceIdentifier();

        if ($identifier === null) {
            throw new \LogicException(
                'Service constructor binding contains no identifier.',
            );
        }

        return $resolvingContainer->get($identifier);
    }

    private function parameterFailure(
        ServiceDefinition $definition,
        ReflectionParameter $parameter,
        string $message,
    ): UnresolvableConstructorParameterException {
        return new UnresolvableConstructorParameterException(
            requestedIdentifier: $definition->identifier(),
            parameterName: $parameter->getName(),
            path: $this->resolutionPath(),
            message: $message,
        );
    }

    private function assertNotCircular(
        ServiceIdentifier $identifier,
    ): void {
        $path = $this->resolutionPath();

        if (!$path->contains($identifier)) {
            return;
        }

        throw new CircularServiceDependencyException(
            requestedIdentifier: $identifier,
            path: $path->append($identifier),
        );
    }

    private function terminalIdentifier(
        ServiceIdentifier $identifier,
    ): ServiceIdentifier {
        $visited = [];
        $current = $identifier;

        while ($this->definitions->has($current)) {
            $value = $current->value();

            if (isset($visited[$value])) {
                throw new CircularServiceDependencyException(
                    requestedIdentifier: $identifier,
                    path: new ResolutionPath([
                        ...array_values($visited),
                        $current,
                    ]),
                );
            }

            $visited[$value] = $current;
            $definition = $this->definitions->get($current);

            if (!$definition->isAlias()) {
                return $current;
            }

            $target = $definition->aliasTarget();

            if ($target === null) {
                return $current;
            }

            $current = $target;
        }

        return $current;
    }
}
