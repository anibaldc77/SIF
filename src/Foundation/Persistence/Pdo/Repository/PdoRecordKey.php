<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Repository;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoRecordKeyException;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;

final readonly class PdoRecordKey
{
    /** @var array<string, int|string> */
    private array $values;

    /** @param array<string, int|string> $values */
    public function __construct(array $values)
    {
        if ($values === []) {
            throw new InvalidPdoRecordKeyException('PDO record key cannot be empty.');
        }

        $normalized = [];
        foreach ($values as $column => $value) {
            $identifier = new PdoSqlIdentifier($column);
            if (count($identifier->segments()) !== 1) {
                throw new InvalidPdoRecordKeyException('PDO record key columns cannot be qualified.');
            }
            $normalized[$identifier->value()] = $value;
        }

        ksort($normalized);
        $this->values = $normalized;
    }

    public static function single(string $column, int|string $value): self
    {
        return new self([$column => $value]);
    }

    /** @return array<string, int|string> */
    public function values(): array
    {
        return $this->values;
    }

    public function count(): int
    {
        return count($this->values);
    }

    /** @return array{columns: list<string>, count: int} */
    public function summary(): array
    {
        return ['columns' => array_keys($this->values), 'count' => count($this->values)];
    }
}
