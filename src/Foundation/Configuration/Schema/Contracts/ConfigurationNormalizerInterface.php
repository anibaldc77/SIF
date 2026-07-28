<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema\Contracts;

interface ConfigurationNormalizerInterface
{
    public function normalize(mixed $value): mixed;
}
