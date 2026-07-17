<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

final readonly class MetadataScanResult
{
    /** @param list<MetadataScanIssue> $issues */
    public function __construct(
        public MetadataRegistry $registry,
        public array $issues,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->issues === [];
    }
}
