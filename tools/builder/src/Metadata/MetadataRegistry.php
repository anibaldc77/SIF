<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

use Sif\Builder\Metadata\Exception\DuplicateMetadataIdentifierException;

final class MetadataRegistry
{
    /** @var array<string, MetadataDocument> */
    private array $documents = [];

    public function register(MetadataDocument $document): void
    {
        $identifier = $document->id();
        if ($identifier === '') {
            return;
        }

        if (isset($this->documents[$identifier])) {
            throw new DuplicateMetadataIdentifierException(
                $identifier,
                $this->documents[$identifier]->path,
                $document->path,
            );
        }

        $this->documents[$identifier] = $document;
    }

    public function has(string $identifier): bool
    {
        return isset($this->documents[$identifier]);
    }

    public function get(string $identifier): ?MetadataDocument
    {
        return $this->documents[$identifier] ?? null;
    }

    /** @return list<MetadataDocument> */
    public function all(): array
    {
        $documents = $this->documents;
        ksort($documents);

        return array_values($documents);
    }

    public function count(): int
    {
        return count($this->documents);
    }
}
