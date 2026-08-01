<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Execution;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoQueryResultException;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\StorageRecord;

final readonly class PdoQueryResult
{
    /** @param ResultSet<StorageRecord> $records */
    public function __construct(
        private ResultSet $records,
        private int $affectedRows,
    ) {
        if ($this->affectedRows < 0) {
            throw new InvalidPdoQueryResultException('Affected row count cannot be negative.');
        }
    }

    /** @return ResultSet<StorageRecord> */
    public function records(): ResultSet
    {
        return $this->records;
    }

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }

    public function isEmpty(): bool
    {
        return $this->records->isEmpty();
    }

    /** @return array{record_count: int, affected_rows: int} */
    public function summary(): array
    {
        return [
            'record_count' => $this->records->count(),
            'affected_rows' => $this->affectedRows,
        ];
    }
}
