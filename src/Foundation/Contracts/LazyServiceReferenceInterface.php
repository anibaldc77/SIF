<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ServiceIdentifier;

interface LazyServiceReferenceInterface
{
    public function identifier(): ServiceIdentifier;

    public function isResolved(): bool;

    public function resolve(): object;
}
