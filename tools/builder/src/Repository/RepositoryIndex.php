<?php

declare(strict_types=1);

namespace Sif\Builder\Repository;

use Sif\Builder\Repository\Exception\DuplicateRepositoryEntryException;

final class RepositoryIndex
{
    /** @var array<string, RepositoryIndexEntry> */
    private array $entries = [];

    public function add(RepositoryIndexEntry $entry): void
    {
        if (isset($this->entries[$entry->identifier])) {
            throw new DuplicateRepositoryEntryException(
                $entry->identifier,
                $this->entries[$entry->identifier]->path,
                $entry->path,
            );
        }

        $this->entries[$entry->identifier] = $entry;
    }

    public function has(string $identifier): bool
    {
        return isset($this->entries[$identifier]);
    }

    public function get(string $identifier): ?RepositoryIndexEntry
    {
        return $this->entries[$identifier] ?? null;
    }

    /** @return list<RepositoryIndexEntry> */
    public function all(): array
    {
        $entries = $this->entries;
        ksort($entries);

        return array_values($entries);
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }
}
