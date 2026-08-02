<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidSkeletonValueException;

final readonly class MigrationTemplateName
{
    public function __construct(private string $value)
    {
        if (preg_match('/^\d{14}_[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/', $value) !== 1) {
            throw new InvalidSkeletonValueException(sprintf(
                'Migration template name "%s" must use YYYYMMDDHHMMSS_snake_case.',
                $value,
            ));
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
