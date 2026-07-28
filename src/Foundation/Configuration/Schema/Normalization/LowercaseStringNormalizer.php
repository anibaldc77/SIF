<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema\Normalization;

use Sif\Foundation\Configuration\Schema\Contracts\ConfigurationNormalizerInterface;

final readonly class LowercaseStringNormalizer implements ConfigurationNormalizerInterface
{
    public function normalize(mixed $value): mixed
    {
        return is_string($value) ? strtolower($value) : $value;
    }
}
