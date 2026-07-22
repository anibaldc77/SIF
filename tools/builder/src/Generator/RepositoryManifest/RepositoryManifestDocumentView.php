<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryManifest;

final readonly class RepositoryManifestDocumentView
{
    /** @param list<string> $tags */
    public function __construct(
        public string $identifier,
        public string $title,
        public string $documentType,
        public string $category,
        public string $status,
        public string $version,
        public string $path,
        public ?string $workPackage,
        public array $tags,
        public int $incomingReferences,
        public int $outgoingReferences,
        public int $brokenReferences,
        public string $entryFingerprint,
    ) {
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier,
            'title' => $this->title,
            'document_type' => $this->documentType,
            'category' => $this->category,
            'status' => $this->status,
            'version' => $this->version,
            'path' => $this->path,
            'work_package' => $this->workPackage,
            'tags' => $this->tags,
            'references' => [
                'incoming' => $this->incomingReferences,
                'outgoing' => $this->outgoingReferences,
                'broken' => $this->brokenReferences,
            ],
            'entry_fingerprint' => [
                'algorithm' => 'sha256',
                'scope' => 'normalized_index_metadata',
                'value' => $this->entryFingerprint,
            ],
        ];
    }
}
