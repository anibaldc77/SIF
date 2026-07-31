<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\History;

use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationHistoryStorageException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final readonly class PdoMigrationHistorySql
{
    public function __construct(
        private PdoMigrationPlatform $platform,
        private PdoMigrationHistoryTable $table,
    ) {
    }

    public function createTable(): string
    {
        $table = $this->table->qualified($this->platform);

        return match ($this->platform->value()) {
            'postgresql' => sprintf(
                'CREATE TABLE IF NOT EXISTS %s ('
                . 'migration_id VARCHAR(190) PRIMARY KEY, '
                . 'checksum VARCHAR(255) NOT NULL, '
                . 'status VARCHAR(32) NOT NULL, '
                . 'recorded_at VARCHAR(64) NOT NULL, '
                . 'migration_version VARCHAR(190) NULL, '
                . 'batch VARCHAR(190) NULL'
                . ')',
                $table,
            ),
            'mysql' => sprintf(
                'CREATE TABLE IF NOT EXISTS %s ('
                . 'migration_id VARCHAR(190) NOT NULL PRIMARY KEY, '
                . 'checksum VARCHAR(255) NOT NULL, '
                . 'status VARCHAR(32) NOT NULL, '
                . 'recorded_at VARCHAR(64) NOT NULL, '
                . 'migration_version VARCHAR(190) NULL, '
                . 'batch VARCHAR(190) NULL'
                . ')',
                $table,
            ),
            'sqlserver' => sprintf(
                "IF OBJECT_ID(N'%s', N'U') IS NULL CREATE TABLE %s ("
                . 'migration_id NVARCHAR(190) NOT NULL PRIMARY KEY, '
                . 'checksum NVARCHAR(255) NOT NULL, '
                . 'status NVARCHAR(32) NOT NULL, '
                . 'recorded_at NVARCHAR(64) NOT NULL, '
                . 'migration_version NVARCHAR(190) NULL, '
                . 'batch NVARCHAR(190) NULL'
                . ')',
                $this->table->logicalName(),
                $table,
            ),
            default => throw new PdoMigrationHistoryStorageException(
                'Unsupported PDO migration platform for history schema creation.',
            ),
        };
    }

    public function selectAll(): string
    {
        return sprintf(
            'SELECT migration_id, checksum, status, recorded_at, migration_version, batch '
            . 'FROM %s ORDER BY migration_id ASC',
            $this->table->qualified($this->platform),
        );
    }

    public function selectOne(): string
    {
        return sprintf(
            'SELECT migration_id, checksum, status, recorded_at, migration_version, batch '
            . 'FROM %s WHERE migration_id = :migration_id',
            $this->table->qualified($this->platform),
        );
    }

    public function insert(): string
    {
        return sprintf(
            'INSERT INTO %s '
            . '(migration_id, checksum, status, recorded_at, migration_version, batch) '
            . 'VALUES (:migration_id, :checksum, :status, :recorded_at, :migration_version, :batch)',
            $this->table->qualified($this->platform),
        );
    }

    public function delete(): string
    {
        return sprintf(
            'DELETE FROM %s WHERE migration_id = :migration_id',
            $this->table->qualified($this->platform),
        );
    }
}
