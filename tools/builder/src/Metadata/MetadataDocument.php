<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

final readonly class MetadataDocument
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $path,
        public array $metadata,
    ) {
    }

    public function id(): string
    {
        $id = $this->metadata['id'] ?? '';

        return is_string($id) ? $id : '';
    }
}
