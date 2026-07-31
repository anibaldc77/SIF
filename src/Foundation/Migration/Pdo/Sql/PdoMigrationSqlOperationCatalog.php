<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Sql;

use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationSqlOperationCatalogException;

final class PdoMigrationSqlOperationCatalog
{
    /** @var array<string, PdoMigrationSqlOperation> */
    private array $operations = [];

    /** @param iterable<PdoMigrationSqlOperation> $operations */
    public function __construct(iterable $operations = [])
    {
        foreach ($operations as $operation) {
            if (!$operation instanceof PdoMigrationSqlOperation) {
                throw new InvalidPdoMigrationSqlOperationCatalogException(
                    'PDO migration SQL operation catalog accepts only PdoMigrationSqlOperation values.',
                );
            }
            $this->register($operation);
        }
    }

    public function register(PdoMigrationSqlOperation $operation): void
    {
        $id = $operation->id()->value();
        if (isset($this->operations[$id])) {
            throw new InvalidPdoMigrationSqlOperationCatalogException(
                sprintf('Duplicate PDO migration SQL operation "%s".', $id),
            );
        }
        $this->operations[$id] = $operation;
    }

    public function has(MigrationId $id): bool
    {
        return isset($this->operations[$id->value()]);
    }

    public function get(MigrationId $id): PdoMigrationSqlOperation
    {
        $operation = $this->operations[$id->value()] ?? null;
        if ($operation === null) {
            throw new InvalidPdoMigrationSqlOperationCatalogException(
                sprintf('PDO migration SQL operation "%s" is not registered.', $id->value()),
            );
        }

        return $operation;
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_keys($this->operations);
    }
}
