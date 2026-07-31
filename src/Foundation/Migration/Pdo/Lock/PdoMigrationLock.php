<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Lock;

use PDO;
use PDOException;
use Sif\Foundation\Migration\Contracts\MigrationLockInterface;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationLockException;
use Throwable;

final class PdoMigrationLock implements MigrationLockInterface
{
    private ?string $owner = null;

    private readonly PdoMigrationLockSql $sql;

    public function __construct(
        private readonly PdoMigrationConnection $connection,
        private readonly PdoMigrationLockResource $resource = new PdoMigrationLockResource(),
        private readonly PdoMigrationLockTimeout $timeout = new PdoMigrationLockTimeout(),
    ) {
        $this->sql = new PdoMigrationLockSql($this->connection->platform());
    }

    public function acquire(string $owner): bool
    {
        $owner = $this->normalizeOwner($owner);

        if ($this->owner !== null) {
            return false;
        }

        try {
            $result = $this->executeScalar($this->sql->acquire(), $this->acquireParameters());
        } catch (PDOException $exception) {
            throw new PdoMigrationLockException('Unable to acquire the PDO migration lock.', 0, $exception);
        }

        if (!$this->acquired($result)) {
            return false;
        }

        $this->owner = $owner;

        return true;
    }

    public function release(string $owner): void
    {
        $owner = $this->normalizeOwner($owner);

        if ($this->owner === null || $this->owner !== $owner) {
            return;
        }

        try {
            $result = $this->executeScalar($this->sql->release(), $this->releaseParameters());
        } catch (PDOException $exception) {
            throw new PdoMigrationLockException('Unable to release the PDO migration lock.', 0, $exception);
        }

        if (!$this->released($result)) {
            throw new PdoMigrationLockException('The database did not confirm PDO migration lock release.');
        }

        $this->owner = null;
    }

    public function owner(): ?string
    {
        return $this->owner;
    }

    public function held(): bool
    {
        return $this->owner !== null;
    }

    /** @return array<string, int|string> */
    private function acquireParameters(): array
    {
        return match ($this->connection->platform()->value()) {
            'postgresql' => $this->postgresqlParameters(),
            'mysql' => [
                'resource' => $this->resource->value(),
                'timeout_seconds' => $this->timeout->mysqlSeconds(),
            ],
            'sqlserver' => [
                'resource' => $this->resource->value(),
                'timeout_milliseconds' => $this->timeout->milliseconds(),
            ],
            default => throw new PdoMigrationLockException('Unsupported PDO migration lock platform.'),
        };
    }

    /** @return array<string, int|string> */
    private function releaseParameters(): array
    {
        return match ($this->connection->platform()->value()) {
            'postgresql' => $this->postgresqlParameters(),
            'mysql', 'sqlserver' => ['resource' => $this->resource->value()],
            default => throw new PdoMigrationLockException('Unsupported PDO migration lock platform.'),
        };
    }

    /** @return array{key_one: int, key_two: int} */
    private function postgresqlParameters(): array
    {
        [$keyOne, $keyTwo] = $this->resource->postgresqlKeys();

        return ['key_one' => $keyOne, 'key_two' => $keyTwo];
    }

    /** @param array<string, int|string> $parameters */
    private function executeScalar(string $sql, array $parameters): mixed
    {
        $statement = $this->connection->pdo()->prepare($sql);

        if ($statement === false) {
            throw new PdoMigrationLockException('Unable to prepare PDO migration lock statement.');
        }

        if (!$statement->execute($parameters)) {
            throw new PdoMigrationLockException('Unable to execute PDO migration lock statement.');
        }

        return $statement->fetchColumn();
    }

    private function acquired(mixed $result): bool
    {
        return match ($this->connection->platform()->value()) {
            'postgresql' => $this->booleanResult($result),
            'mysql' => (int) $result === 1,
            'sqlserver' => (int) $result >= 0,
            default => false,
        };
    }

    private function released(mixed $result): bool
    {
        return match ($this->connection->platform()->value()) {
            'postgresql' => $this->booleanResult($result),
            'mysql' => (int) $result === 1,
            'sqlserver' => (int) $result >= 0,
            default => false,
        };
    }

    private function booleanResult(mixed $result): bool
    {
        return $result === true || $result === 1 || $result === '1' || $result === 't' || $result === 'true';
    }

    private function normalizeOwner(string $owner): string
    {
        $owner = trim($owner);

        if ($owner === '') {
            throw new PdoMigrationLockException('PDO migration lock owner must not be empty.');
        }

        return $owner;
    }
}
