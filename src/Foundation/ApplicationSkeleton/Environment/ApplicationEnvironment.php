<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Environment;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class ApplicationEnvironment
{
    public function __construct(private string $name)
    {
        if (preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $name) !== 1) {
            throw new InvalidSkeletonValueException(sprintf('Invalid application environment "%s".', $name));
        }
    }

    public function name(): string
    {
        return $this->name;
    }
}
