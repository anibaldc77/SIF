<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class ProjectIdentifier
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $value) !== 1) {
            throw new InvalidSkeletonValueException(
                sprintf('Invalid project identifier "%s".', $value),
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
