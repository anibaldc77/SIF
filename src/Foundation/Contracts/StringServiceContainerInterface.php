<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface StringServiceContainerInterface
{
    public function has(string $identifier): bool;

    public function get(string $identifier): object;

    public function lazy(string $identifier): LazyServiceReferenceInterface;

    public function beginScope(string $identifier): self;
}
