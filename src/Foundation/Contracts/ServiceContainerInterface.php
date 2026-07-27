<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ServiceIdentifier;

interface ServiceContainerInterface
{
    public function has(ServiceIdentifier $identifier): bool;

    public function get(ServiceIdentifier $identifier): object;

    public function lazy(
        ServiceIdentifier $identifier,
    ): LazyServiceReferenceInterface;
}
