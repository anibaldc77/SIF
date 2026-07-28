<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

interface FailureMetadataEnricherInterface
{
    /** @param array<string, mixed> $metadata
     *  @return array<string, mixed>
     */
    public function enrich(array $metadata): array;
}
