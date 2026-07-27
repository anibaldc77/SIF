<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Memory;

use Sif\Foundation\Persistence\StorageRecord;

final class InMemoryStorage
{
    /**
     * @var array<string, array<string, StorageRecord>>
     */
    private array $collections = [];

    public function put(
        string $collection,
        string|int $identifier,
        StorageRecord $record,
    ): void {
        $this->collections[$collection][(string) $identifier] = $record;
    }

    public function get(
        string $collection,
        string|int $identifier,
    ): ?StorageRecord {
        return $this->collections[$collection][(string) $identifier] ?? null;
    }

    public function remove(
        string $collection,
        string|int $identifier,
    ): void {
        unset($this->collections[$collection][(string) $identifier]);
    }

    /**
     * @return list<StorageRecord>
     */
    public function all(string $collection): array
    {
        return array_values($this->collections[$collection] ?? []);
    }

    public function count(string $collection): int
    {
        return count($this->collections[$collection] ?? []);
    }

    public function clear(string $collection): void
    {
        $this->collections[$collection] = [];
    }
}
