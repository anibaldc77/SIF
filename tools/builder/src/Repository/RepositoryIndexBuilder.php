<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final class RepositoryIndexBuilder
{
    public function build(MetadataRegistry $registry): RepositoryIndex
    {
        $index = new RepositoryIndex();

        foreach ($registry->all() as $document) {
            $index->add($this->createEntry($document));
        }

        return $index;
    }

    private function createEntry(MetadataDocument $document): RepositoryIndexEntry
    {
        $metadata = $document->metadata;

        return new RepositoryIndexEntry(
            identifier: $this->stringValue($metadata, 'id'),
            title: $this->stringValue($metadata, 'title'),
            documentClass: $this->stringValue($metadata, 'document_class'),
            category: $this->stringValue($metadata, 'category'),
            status: $this->stringValue($metadata, 'status'),
            version: $this->stringValue($metadata, 'version'),
            path: $document->path,
            workPackage: $this->nullableStringValue($metadata, 'work_package'),
            tags: $this->stringListValue($metadata, 'tags'),
        );
    }

    /** @param array<string, mixed> $metadata */
    private function stringValue(array $metadata, string $key): string
    {
        $value = $metadata[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /** @param array<string, mixed> $metadata */
    private function nullableStringValue(array $metadata, string $key): ?string
    {
        $value = $metadata[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $metadata
     * @return list<string>
     */
    private function stringListValue(array $metadata, string $key): array
    {
        $value = $metadata[$key] ?? [];
        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
