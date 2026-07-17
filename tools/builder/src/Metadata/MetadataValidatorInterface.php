<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

interface MetadataValidatorInterface
{
    /** @param array<string, mixed> $metadata */
    public function validate(array $metadata): MetadataValidationResult;
}
