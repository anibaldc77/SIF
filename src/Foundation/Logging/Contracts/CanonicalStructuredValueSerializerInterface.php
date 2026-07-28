<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

interface CanonicalStructuredValueSerializerInterface
{
    /**
     * @param null|bool|int|float|string|array<mixed> $value
     */
    public function serialize(null|bool|int|float|string|array $value): string;
}
