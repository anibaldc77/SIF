<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\History;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationHistoryException;
use Sif\Foundation\Migration\MigrationId;

final readonly class MigrationHistory
{
    /** @var array<string, MigrationHistoryRecord> */
    private array $records;

    /** @param iterable<MigrationHistoryRecord> $records */
    public function __construct(iterable $records = [])
    {
        $normalized = [];
        foreach ($records as $record) {
            if (!$record instanceof MigrationHistoryRecord) {
                throw new InvalidMigrationHistoryException(
                    'Migration history members must be MigrationHistoryRecord values.',
                );
            }

            $id = $record->id()->value();
            if (isset($normalized[$id])) {
                throw new InvalidMigrationHistoryException(
                    sprintf('Migration history contains duplicate migration "%s".', $id),
                );
            }
            $normalized[$id] = $record;
        }

        ksort($normalized, SORT_STRING);
        $this->records = $normalized;
    }

    public function find(MigrationId $id): ?MigrationHistoryRecord
    {
        return $this->records[$id->value()] ?? null;
    }

    public function contains(MigrationId $id): bool
    {
        return isset($this->records[$id->value()]);
    }

    /** @return list<MigrationHistoryRecord> */
    public function records(): array
    {
        return array_values($this->records);
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_keys($this->records);
    }

    public function count(): int
    {
        return count($this->records);
    }
}
