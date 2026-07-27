<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Contracts\LazyServiceReferenceInterface;
use Sif\Foundation\Contracts\ServiceContainerInterface;

final class LazyServiceReference implements LazyServiceReferenceInterface
{
    private ?object $resolved = null;

    public function __construct(
        private readonly ServiceContainerInterface $container,
        private readonly ServiceIdentifier $identifier,
    ) {
    }

    public function identifier(): ServiceIdentifier
    {
        return $this->identifier;
    }

    public function isResolved(): bool
    {
        return $this->resolved !== null;
    }

    public function resolve(): object
    {
        if ($this->resolved === null) {
            $this->resolved = $this->container->get($this->identifier);
        }

        return $this->resolved;
    }
}
