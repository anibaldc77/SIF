<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class ProjectPath
{
    public function __construct(private string $value)
    {
        if (
            $value === ''
            || str_contains($value, '\\')
            || str_starts_with($value, '/')
            || preg_match('/^[A-Za-z]:/', $value) === 1
            || preg_match('#(^|/)\.\.(/|$)#', $value) === 1
            || preg_match('#(^|/)\.(/|$)#', $value) === 1
            || str_contains($value, '//')
            || preg_match('/[\x00-\x1F\x7F]/', $value) === 1
        ) {
            throw new InvalidSkeletonValueException(
                sprintf('Invalid portable project path "%s".', $value),
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
