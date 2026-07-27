<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Exceptions\ClosedServiceScopeException;

final class ServiceScopeState
{
    /**
     * @var array<string, object>
     */
    private array $instances = [];

    private bool $closed = false;

    public function __construct(
        private readonly ScopeIdentifier $identifier,
        private readonly ?self $parent = null,
    ) {
    }

    public function identifier(): ScopeIdentifier
    {
        return $this->identifier;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function assertOpen(): void
    {
        if ($this->closed) {
            throw new ClosedServiceScopeException(
                sprintf(
                    'Service scope "%s" is closed.',
                    $this->identifier->value(),
                ),
            );
        }

        $this->parent?->assertOpen();
    }

    public function has(ServiceIdentifier $identifier): bool
    {
        $this->assertOpen();

        return isset($this->instances[$identifier->value()]);
    }

    public function get(ServiceIdentifier $identifier): object
    {
        $this->assertOpen();

        if (!isset($this->instances[$identifier->value()])) {
            throw new \LogicException(
                sprintf(
                    'Scoped instance "%s" is not stored.',
                    $identifier->value(),
                ),
            );
        }

        return $this->instances[$identifier->value()];
    }

    public function put(
        ServiceIdentifier $identifier,
        object $service,
    ): void {
        $this->assertOpen();

        $this->instances[$identifier->value()] = $service;
    }

    public function forget(ServiceIdentifier $identifier): void
    {
        $this->assertOpen();

        unset($this->instances[$identifier->value()]);
    }

    public function clear(): void
    {
        $this->assertOpen();

        $this->instances = [];
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->instances = [];
        $this->closed = true;
    }
}
