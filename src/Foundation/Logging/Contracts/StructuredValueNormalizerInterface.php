<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

interface StructuredValueNormalizerInterface
{
    /**
     * @return null|bool|int|float|string|array<mixed>
     */
    public function normalize(mixed $value): null|bool|int|float|string|array;
}
