<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

final readonly class MetadataScanIssue
{
    public function __construct(
        public string $path,
        public string $message,
    ) {
    }
}
