<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

final readonly class MetadataValidationError
{
    public function __construct(
        public string $code,
        public string $path,
        public string $message,
    ) {
        if ($code === '' || $path === '' || $message === '') {
            throw new \InvalidArgumentException('Validation error fields cannot be empty.');
        }
    }
}
