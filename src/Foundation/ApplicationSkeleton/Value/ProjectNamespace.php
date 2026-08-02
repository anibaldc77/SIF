<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class ProjectNamespace
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[A-Z][A-Za-z0-9]*(?:\\\\[A-Z][A-Za-z0-9]*)*$/', $value) !== 1) {
            throw new InvalidSkeletonValueException(
                sprintf('Invalid project namespace "%s".', $value),
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
