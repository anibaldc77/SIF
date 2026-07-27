<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Contracts\LazyServiceReferenceInterface;
use Sif\Foundation\Contracts\ScopedServiceContainerInterface;
use Sif\Foundation\Contracts\StringServiceContainerInterface;

final readonly class StringServiceContainerAdapter implements
    StringServiceContainerInterface
{
    public function __construct(
        private ScopedServiceContainerInterface $container,
    ) {
    }

    public function has(string $identifier): bool
    {
        return $this->container->has(
            new ServiceIdentifier($identifier),
        );
    }

    public function get(string $identifier): object
    {
        return $this->container->get(
            new ServiceIdentifier($identifier),
        );
    }

    public function lazy(
        string $identifier,
    ): LazyServiceReferenceInterface {
        return $this->container->lazy(
            new ServiceIdentifier($identifier),
        );
    }

    public function beginScope(string $identifier): self
    {
        return new self(
            $this->container->beginScope(
                new ScopeIdentifier($identifier),
            ),
        );
    }

    public function native(): ScopedServiceContainerInterface
    {
        return $this->container;
    }
}
