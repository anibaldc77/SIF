<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\History;

use DateTimeImmutable;
use PDO;
use PDOException;
use PDOStatement;
use Sif\Foundation\Migration\Contracts\MigrationHistoryStoreInterface;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationVersion;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationHistoryStorageException;
use Throwable;

final class PdoMigrationHistoryStore implements MigrationHistoryStoreInterface
{
    private readonly PDO $pdo;

    private readonly PdoMigrationHistorySql $sql;

    private bool $initialized = false;

    public function __construct(
        PdoMigrationConnection $connection,
        ?PdoMigrationHistoryTable $table = null,
        private readonly bool $autoInitialize = true,
    ) {
        $this->pdo = $connection->pdo();
        $this->sql = new PdoMigrationHistorySql(
            $connection->platform(),
            $table ?? new PdoMigrationHistoryTable(),
        );
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        try {
            $result = $this->pdo->exec($this->sql->createTable());
            if ($result === false) {
                throw new PdoMigrationHistoryStorageException(
                    'PDO migration history table initialization returned failure.',
                );
            }
            $this->initialized = true;
        } catch (PdoMigrationHistoryStorageException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new PdoMigrationHistoryStorageException(
                'Unable to initialize the PDO migration history table.',
                0,
                $exception,
            );
        }
    }

    public function history(): MigrationHistory
    {
        $this->initializeWhenRequired();

        try {
            $statement = $this->pdo->query($this->sql->selectAll());
            if (!$statement instanceof PDOStatement) {
                throw new PdoMigrationHistoryStorageException(
                    'PDO migration history query could not be prepared.',
                );
            }

            /** @var array<int, array<string, mixed>> $rows */
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

            return new MigrationHistory(array_map($this->hydrate(...), $rows));
        } catch (PdoMigrationHistoryStorageException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new PdoMigrationHistoryStorageException(
                'Unable to load PDO migration history.',
                0,
                $exception,
            );
        }
    }

    public function find(MigrationId $id): ?MigrationHistoryRecord
    {
        $this->initializeWhenRequired();

        try {
            $statement = $this->prepare($this->sql->selectOne());
            $this->execute($statement, ['migration_id' => $id->value()]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                return null;
            }
            if (!is_array($row)) {
                throw new PdoMigrationHistoryStorageException(
                    'PDO migration history lookup returned an invalid row.',
                );
            }

            /** @var array<string, mixed> $row */
            return $this->hydrate($row);
        } catch (PdoMigrationHistoryStorageException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new PdoMigrationHistoryStorageException(
                sprintf('Unable to find PDO migration history record "%s".', $id->value()),
                0,
                $exception,
            );
        }
    }

    public function append(MigrationHistoryRecord $record): void
    {
        $this->initializeWhenRequired();

        try {
            $statement = $this->prepare($this->sql->insert());
            $this->execute($statement, [
                'migration_id' => $record->id()->value(),
                'checksum' => $record->checksum()->value(),
                'status' => $record->status()->value(),
                'recorded_at' => $record->recordedAt()->format(DATE_ATOM),
                'migration_version' => $record->version()?->value(),
                'batch' => $record->batch(),
            ]);
        } catch (PdoMigrationHistoryStorageException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new PdoMigrationHistoryStorageException(
                sprintf('Unable to append PDO migration history record "%s".', $record->id()->value()),
                0,
                $exception,
            );
        }
    }

    public function remove(MigrationId $id): void
    {
        $this->initializeWhenRequired();

        try {
            $statement = $this->prepare($this->sql->delete());
            $this->execute($statement, ['migration_id' => $id->value()]);
        } catch (PdoMigrationHistoryStorageException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new PdoMigrationHistoryStorageException(
                sprintf('Unable to remove PDO migration history record "%s".', $id->value()),
                0,
                $exception,
            );
        }
    }

    private function initializeWhenRequired(): void
    {
        if ($this->autoInitialize) {
            $this->initialize();
        }
    }

    private function prepare(string $sql): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        if (!$statement instanceof PDOStatement) {
            throw new PdoMigrationHistoryStorageException(
                'PDO migration history statement could not be prepared.',
            );
        }

        return $statement;
    }

    /** @param array<string, string|null> $parameters */
    private function execute(PDOStatement $statement, array $parameters): void
    {
        if (!$statement->execute($parameters)) {
            throw new PdoMigrationHistoryStorageException(
                'PDO migration history statement execution returned failure.',
            );
        }
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): MigrationHistoryRecord
    {
        try {
            $id = $this->requiredString($row, 'migration_id');
            $checksum = $this->requiredString($row, 'checksum');
            $status = $this->requiredString($row, 'status');
            $recordedAt = $this->requiredString($row, 'recorded_at');
            $version = $this->nullableString($row, 'migration_version');
            $batch = $this->nullableString($row, 'batch');

            return new MigrationHistoryRecord(
                new MigrationId($id),
                MigrationChecksum::parse($checksum),
                MigrationHistoryStatus::from($status),
                new DateTimeImmutable($recordedAt),
                $version === null ? null : new MigrationVersion($version),
                $batch,
            );
        } catch (PdoMigrationHistoryStorageException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new PdoMigrationHistoryStorageException(
                'PDO migration history row contains invalid governed data.',
                0,
                $exception,
            );
        }
    }

    /** @param array<string, mixed> $row */
    private function requiredString(array $row, string $field): string
    {
        $value = $row[$field] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new PdoMigrationHistoryStorageException(
                sprintf('PDO migration history field "%s" must be a non-empty string.', $field),
            );
        }

        return $value;
    }

    /** @param array<string, mixed> $row */
    private function nullableString(array $row, string $field): ?string
    {
        $value = $row[$field] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new PdoMigrationHistoryStorageException(
                sprintf('PDO migration history field "%s" must be a string or null.', $field),
            );
        }

        return $value;
    }
}
