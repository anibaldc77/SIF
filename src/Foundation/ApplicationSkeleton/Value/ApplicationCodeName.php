<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class ApplicationCodeName
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $value) !== 1) {
            throw new InvalidSkeletonValueException(sprintf(
                'Application code name "%s" must use PascalCase letters and digits.',
                $value,
            ));
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function snakeCase(): string
    {
        $snake = preg_replace('/(?<!^)[A-Z]/', '_$0', $this->value);
        if ($snake === null) {
            throw new InvalidSkeletonValueException('Unable to normalize application code name.');
        }

        return strtolower($snake);
    }
}
