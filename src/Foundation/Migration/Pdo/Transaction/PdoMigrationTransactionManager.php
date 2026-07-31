<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Transaction;

use PDOException;
use Sif\Foundation\Migration\Contracts\MigrationTransactionManagerInterface;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationTransactionException;

final class PdoMigrationTransactionManager implements MigrationTransactionManagerInterface
{
    private const STATE_IDLE = 'idle';
    private const STATE_OWNED = 'owned';
    private const STATE_SAVEPOINT = 'savepoint';

    private string $state = self::STATE_IDLE;

    private readonly string $savepoint;

    public function __construct(
        private readonly PdoMigrationConnection $connection,
        private readonly PdoMigrationExternalTransactionPolicy $externalTransactionPolicy = new PdoMigrationExternalTransactionPolicy(),
        string $savepoint = 'sif_migration',
    ) {
        $savepoint = trim($savepoint);

        if (preg_match('/^[A-Za-z][A-Za-z0-9_]{0,62}$/D', $savepoint) !== 1) {
            throw new PdoMigrationTransactionException('Migration savepoint must be a safe SQL identifier.');
        }

        $this->savepoint = $savepoint;
    }

    public function supportsTransactions(): bool
    {
        return $this->connection->capabilities()->transactionsSupported();
    }

    public function begin(): void
    {
        if (!$this->supportsTransactions()) {
            throw new PdoMigrationTransactionException('PDO migration transactions are not supported by this platform profile.');
        }

        if ($this->state !== self::STATE_IDLE) {
            throw new PdoMigrationTransactionException('PDO migration transaction manager is already active.');
        }

        try {
            if ($this->connection->pdo()->inTransaction()) {
                $this->beginInsideExternalTransaction();
                return;
            }

            if (!$this->connection->pdo()->beginTransaction()) {
                throw new PdoMigrationTransactionException('PDO did not confirm migration transaction start.');
            }

            $this->state = self::STATE_OWNED;
        } catch (PDOException $exception) {
            throw new PdoMigrationTransactionException('Unable to begin PDO migration transaction.', 0, $exception);
        }
    }

    public function commit(): void
    {
        $this->assertActive('commit');

        try {
            if ($this->state === self::STATE_SAVEPOINT) {
                $this->assertDatabaseTransactionActive();
                $this->execute('RELEASE SAVEPOINT ' . $this->savepoint);
                $this->state = self::STATE_IDLE;
                return;
            }

            $this->assertDatabaseTransactionActive();
            if (!$this->connection->pdo()->commit()) {
                throw new PdoMigrationTransactionException('PDO did not confirm migration transaction commit.');
            }

            $this->state = self::STATE_IDLE;
        } catch (PDOException $exception) {
            throw new PdoMigrationTransactionException('Unable to commit PDO migration transaction.', 0, $exception);
        }
    }

    public function rollBack(): void
    {
        $this->assertActive('roll back');

        try {
            if ($this->state === self::STATE_SAVEPOINT) {
                $this->assertDatabaseTransactionActive();
                $this->execute('ROLLBACK TO SAVEPOINT ' . $this->savepoint);
                $this->execute('RELEASE SAVEPOINT ' . $this->savepoint);
                $this->state = self::STATE_IDLE;
                return;
            }

            $this->assertDatabaseTransactionActive();
            if (!$this->connection->pdo()->rollBack()) {
                throw new PdoMigrationTransactionException('PDO did not confirm migration transaction rollback.');
            }

            $this->state = self::STATE_IDLE;
        } catch (PDOException $exception) {
            throw new PdoMigrationTransactionException('Unable to roll back PDO migration transaction.', 0, $exception);
        }
    }

    public function active(): bool
    {
        return $this->state !== self::STATE_IDLE;
    }

    public function ownsTransaction(): bool
    {
        return $this->state === self::STATE_OWNED;
    }

    public function usesSavepoint(): bool
    {
        return $this->state === self::STATE_SAVEPOINT;
    }

    public function state(): string
    {
        return $this->state;
    }

    private function beginInsideExternalTransaction(): void
    {
        if ($this->externalTransactionPolicy->rejectsExternalTransaction()) {
            throw new PdoMigrationTransactionException(
                'An external PDO transaction is already active and the configured policy rejects participation.',
            );
        }

        if (!$this->connection->capabilities()->savepointsSupported()) {
            throw new PdoMigrationTransactionException(
                'External transaction participation requires savepoint support.',
            );
        }

        $this->execute('SAVEPOINT ' . $this->savepoint);
        $this->state = self::STATE_SAVEPOINT;
    }

    private function assertActive(string $operation): void
    {
        if ($this->state === self::STATE_IDLE) {
            throw new PdoMigrationTransactionException(
                sprintf('Cannot %s because no PDO migration transaction is active.', $operation),
            );
        }
    }

    private function assertDatabaseTransactionActive(): void
    {
        if (!$this->connection->pdo()->inTransaction()) {
            throw new PdoMigrationTransactionException(
                'PDO migration transaction state diverged from the database connection state.',
            );
        }
    }

    private function execute(string $sql): void
    {
        if ($this->connection->pdo()->exec($sql) === false) {
            throw new PdoMigrationTransactionException('PDO did not confirm migration savepoint operation.');
        }
    }
}
