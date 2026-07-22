<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryManifest;

final readonly class RepositoryManifestView
{
    /**
     * @param list<RepositoryManifestDocumentView> $documents
     * @param list<RepositoryManifestReferenceView> $references
     * @param array<string, int> $documentsByType
     * @param array<string, int> $documentsByStatus
     */
    public function __construct(
        public array $documents,
        public array $references,
        public array $documentsByType,
        public array $documentsByStatus,
    ) {
    }

    public function resolvedReferenceCount(): int
    {
        return count(array_filter($this->references, static fn (RepositoryManifestReferenceView $reference): bool => $reference->resolved));
    }

    public function brokenReferenceCount(): int
    {
        return count($this->references) - $this->resolvedReferenceCount();
    }
}
