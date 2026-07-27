<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Closure;
use Sif\Foundation\Contracts\ServiceContainerInterface;
use Sif\Foundation\Exceptions\InvalidServiceDefinitionException;

final readonly class ServiceDefinition
{
    /**
     * @param class-string|null $className
     * @param Closure(ServiceContainerInterface): object|null $factory
     * @param list<ServiceTag> $tags
     */
    private function __construct(
        private ServiceIdentifier $identifier,
        private ServiceDefinitionKind $kind,
        private ?ServiceLifetime $lifetime,
        private ?string $className,
        private ?Closure $factory,
        private ?object $instance,
        private ?ServiceIdentifier $aliasTarget,
        private bool $autowire,
        private ConstructorArgumentBindings $constructorBindings,
        private array $tags,
    ) {
        $this->assertValid();
    }

    /**
     * @param class-string $className
     * @param list<ServiceTag> $tags
     */
    public static function forClass(
        ServiceIdentifier $identifier,
        string $className,
        ServiceLifetime $lifetime = ServiceLifetime::Transient,
        array $tags = [],
    ): self {
        return self::classDefinition(
            identifier: $identifier,
            className: $className,
            lifetime: $lifetime,
            autowire: false,
            constructorBindings: new ConstructorArgumentBindings(),
            tags: $tags,
        );
    }

    /**
     * @param class-string $className
     * @param list<ServiceTag> $tags
     */
    public static function forAutowiredClass(
        ServiceIdentifier $identifier,
        string $className,
        ServiceLifetime $lifetime = ServiceLifetime::Transient,
        ?ConstructorArgumentBindings $constructorBindings = null,
        array $tags = [],
    ): self {
        return self::classDefinition(
            identifier: $identifier,
            className: $className,
            lifetime: $lifetime,
            autowire: true,
            constructorBindings: $constructorBindings
                ?? new ConstructorArgumentBindings(),
            tags: $tags,
        );
    }

    /**
     * @param callable(ServiceContainerInterface): object $factory
     * @param list<ServiceTag> $tags
     */
    public static function forFactory(
        ServiceIdentifier $identifier,
        callable $factory,
        ServiceLifetime $lifetime = ServiceLifetime::Transient,
        array $tags = [],
    ): self {
        return new self(
            identifier: $identifier,
            kind: ServiceDefinitionKind::Factory,
            lifetime: $lifetime,
            className: null,
            factory: Closure::fromCallable($factory),
            instance: null,
            aliasTarget: null,
            autowire: false,
            constructorBindings: new ConstructorArgumentBindings(),
            tags: array_values($tags),
        );
    }

    /**
     * @param list<ServiceTag> $tags
     */
    public static function forInstance(
        ServiceIdentifier $identifier,
        object $instance,
        array $tags = [],
    ): self {
        return new self(
            identifier: $identifier,
            kind: ServiceDefinitionKind::Instance,
            lifetime: ServiceLifetime::Singleton,
            className: null,
            factory: null,
            instance: $instance,
            aliasTarget: null,
            autowire: false,
            constructorBindings: new ConstructorArgumentBindings(),
            tags: array_values($tags),
        );
    }

    public static function alias(
        ServiceIdentifier $identifier,
        ServiceIdentifier $target,
    ): self {
        if ($identifier->equals($target)) {
            throw new InvalidServiceDefinitionException(
                sprintf(
                    'Service alias "%s" cannot target itself.',
                    $identifier->value(),
                ),
            );
        }

        return new self(
            identifier: $identifier,
            kind: ServiceDefinitionKind::Alias,
            lifetime: null,
            className: null,
            factory: null,
            instance: null,
            aliasTarget: $target,
            autowire: false,
            constructorBindings: new ConstructorArgumentBindings(),
            tags: [],
        );
    }

    public function identifier(): ServiceIdentifier
    {
        return $this->identifier;
    }

    public function kind(): ServiceDefinitionKind
    {
        return $this->kind;
    }

    public function lifetime(): ?ServiceLifetime
    {
        return $this->lifetime;
    }

    /**
     * @return class-string|null
     */
    public function className(): ?string
    {
        return $this->className;
    }

    /**
     * @return Closure(ServiceContainerInterface): object|null
     */
    public function factory(): ?Closure
    {
        return $this->factory;
    }

    public function instance(): ?object
    {
        return $this->instance;
    }

    public function aliasTarget(): ?ServiceIdentifier
    {
        return $this->aliasTarget;
    }

    public function isAlias(): bool
    {
        return $this->kind === ServiceDefinitionKind::Alias;
    }

    public function autowire(): bool
    {
        return $this->autowire;
    }

    public function constructorBindings(): ConstructorArgumentBindings
    {
        return $this->constructorBindings;
    }

    /**
     * @return list<ServiceTag>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @param class-string $className
     * @param list<ServiceTag> $tags
     */
    private static function classDefinition(
        ServiceIdentifier $identifier,
        string $className,
        ServiceLifetime $lifetime,
        bool $autowire,
        ConstructorArgumentBindings $constructorBindings,
        array $tags,
    ): self {
        if (trim($className) === '') {
            throw new InvalidServiceDefinitionException(
                'Service class name cannot be empty.',
            );
        }

        return new self(
            identifier: $identifier,
            kind: ServiceDefinitionKind::ClassType,
            lifetime: $lifetime,
            className: $className,
            factory: null,
            instance: null,
            aliasTarget: null,
            autowire: $autowire,
            constructorBindings: $constructorBindings,
            tags: array_values($tags),
        );
    }

    private function assertValid(): void
    {
        $strategyCount = 0;

        if ($this->className !== null) {
            $strategyCount++;
        }

        if ($this->factory !== null) {
            $strategyCount++;
        }

        if ($this->instance !== null) {
            $strategyCount++;
        }

        if ($this->aliasTarget !== null) {
            $strategyCount++;
        }

        if ($strategyCount !== 1) {
            throw new InvalidServiceDefinitionException(
                sprintf(
                    'Service definition "%s" must declare exactly one resolution strategy.',
                    $this->identifier->value(),
                ),
            );
        }

        if (
            $this->kind === ServiceDefinitionKind::Alias
            && $this->lifetime !== null
        ) {
            throw new InvalidServiceDefinitionException(
                'Alias definitions cannot declare a lifetime.',
            );
        }

        if (
            $this->kind !== ServiceDefinitionKind::Alias
            && $this->lifetime === null
        ) {
            throw new InvalidServiceDefinitionException(
                'Non-alias definitions must declare a lifetime.',
            );
        }

        if (
            $this->kind !== ServiceDefinitionKind::ClassType
            && (
                $this->autowire
                || !$this->constructorBindings->isEmpty()
            )
        ) {
            throw new InvalidServiceDefinitionException(
                'Only class definitions may declare constructor autowiring or bindings.',
            );
        }

        if (
            $this->kind === ServiceDefinitionKind::Alias
            && $this->tags !== []
        ) {
            throw new InvalidServiceDefinitionException(
                'Alias definitions cannot declare tags.',
            );
        }
    }
}
