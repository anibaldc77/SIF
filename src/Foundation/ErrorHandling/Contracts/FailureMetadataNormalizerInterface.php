<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

interface FailureMetadataNormalizerInterface
{
    /** @param array<string, mixed> $metadata
     *  @return array<string, null|bool|int|float|string|array<mixed>>
     */
    public function normalize(array $metadata): array;
}
