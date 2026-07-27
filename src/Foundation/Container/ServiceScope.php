<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Contracts\ServiceScopeInterface;
use Sif\Foundation\Contracts\TaggedServiceLocatorInterface;

final class ServiceScope implements
    ServiceScopeInterface,
    TaggedServiceLocatorInterface
{
    public function __construct(
        private readonly DefinitionServiceContainer $container,
        private readonly ServiceScopeState $state,
        private readonly ?ServiceScopeInterface $parentScope = null,
    ) {
    }

    public function identifier(): ScopeIdentifier
    {
        return $this->state->identifier();
    }

    public function parent(): ?ServiceScopeInterface
    {
        return $this->parentScope;
    }

    public function isClosed(): bool
    {
        return $this->state->isClosed();
    }

    public function close(): void
    {
        $this->state->close();
    }

    public function has(ServiceIdentifier $identifier): bool
    {
        $this->state->assertOpen();

        return $this->container->has($identifier);
    }

    public function get(ServiceIdentifier $identifier): object
    {
        $this->state->assertOpen();

        return $this->container->getWithinScope(
            identifier: $identifier,
            state: $this->state,
            scopedContainer: $this,
        );
    }

    public function lazy(
        ServiceIdentifier $identifier,
    ): \Sif\Foundation\Contracts\LazyServiceReferenceInterface {
        $this->state->assertOpen();

        return new LazyServiceReference($this, $identifier);
    }

    public function resolutionPath(): ResolutionPath
    {
        $this->state->assertOpen();

        return $this->container->resolutionPath();
    }

    public function forget(ServiceIdentifier $identifier): void
    {
        $this->state->assertOpen();

        $this->container->forgetWithinScope(
            identifier: $identifier,
            state: $this->state,
        );
    }

    public function clearSingletons(): void
    {
        $this->state->assertOpen();

        $this->container->clearSingletons();
    }

    public function beginScope(
        ScopeIdentifier $identifier,
    ): ServiceScopeInterface {
        $this->state->assertOpen();

        return new self(
            container: $this->container,
            state: new ServiceScopeState(
                identifier: $identifier,
                parent: $this->state,
            ),
            parentScope: $this,
        );
    }

    /**
     * @return list<TaggedService>
     */
    public function tagged(string $tag): array
    {
        $this->state->assertOpen();

        return $this->container->tagged($tag);
    }

    /**
     * @return list<object>
     */
    public function resolveTagged(string $tag): array
    {
        $this->state->assertOpen();

        return array_map(
            fn (TaggedService $service): object => $this->get(
                $service->identifier(),
            ),
            $this->tagged($tag),
        );
    }
}
