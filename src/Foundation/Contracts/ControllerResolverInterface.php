<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface ControllerResolverInterface
{
    public function has(string $identifier): bool;

    public function resolve(string $identifier): object;
}
