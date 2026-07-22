<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceGraph;

final readonly class ReferenceGraphNodeView
{
    public function __construct(
        public string $identifier,
        public string $title,
        public string $documentType,
        public string $status,
        public string $version,
        public int $incoming,
        public int $outgoing,
    ) {
    }

    /** @return array<string, int|string> */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'title' => $this->title,
            'document_type' => $this->documentType,
            'status' => $this->status,
            'version' => $this->version,
            'incoming' => $this->incoming,
            'outgoing' => $this->outgoing,
        ];
    }
}
