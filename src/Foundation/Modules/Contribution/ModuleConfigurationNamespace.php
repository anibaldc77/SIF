<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contribution;

use Sif\Foundation\Modules\Exceptions\InvalidModuleContributionException;

final readonly class ModuleConfigurationNamespace
{
    public function __construct(private string $value)
    {
        if (!preg_match('/^[a-z][a-z0-9]*(?:\.[a-z][a-z0-9_-]*)*$/', $value)) {
            throw InvalidModuleContributionException::invalidConfigurationNamespace($value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
