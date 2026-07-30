<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Adapter;

use Sif\Foundation\Migration\Contracts\MigrationHistoryStoreInterface;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\MigrationId;

final class InMemoryMigrationHistoryStore implements MigrationHistoryStoreInterface
{
    /** @var array<string, MigrationHistoryRecord> */
    private array $records = [];

    /** @param iterable<MigrationHistoryRecord> $records */
    public function __construct(iterable $records = [])
    {
        foreach ($records as $record) {
            $this->append($record);
        }
    }

    public function history(): MigrationHistory
    {
        return new MigrationHistory(array_values($this->records));
    }

    public function find(MigrationId $id): ?MigrationHistoryRecord
    {
        return $this->records[$id->value()] ?? null;
    }

    public function append(MigrationHistoryRecord $record): void
    {
        $this->records[$record->id()->value()] = $record;
        ksort($this->records, SORT_STRING);
    }

    public function remove(MigrationId $id): void
    {
        unset($this->records[$id->value()]);
    }
}
