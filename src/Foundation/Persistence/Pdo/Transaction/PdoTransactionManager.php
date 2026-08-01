<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Transaction;

use PDO;
use PDOException;
use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Exceptions\NestedTransactionNotSupportedException;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Exception\PdoTransactionException;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\TransactionState;
use Throwable;

final class PdoTransactionManager implements TransactionManagerInterface, PersistenceCapabilityProviderInterface
{
    private TransactionState $state = TransactionState::Idle;
    private int $depth = 0;
    private PdoTransactionScope $scope = PdoTransactionScope::None;

    public function __construct(
        private readonly PdoPersistenceConnection $connection,
        private readonly PdoExternalTransactionPolicy $externalPolicy = PdoExternalTransactionPolicy::Reject,
        private readonly string $savepointName = 'sif_persistence_scope',
    ) {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $this->savepointName) !== 1) {
            throw new PdoTransactionException('Savepoint name must be a safe SQL identifier.');
        }
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function transactional(callable $operation): mixed
    {
        if (!$this->connection->capabilities()->transactionsSupported()) {
            throw new PdoTransactionException('PDO persistence connection does not support transactions.');
        }
        if ($this->depth > 0) {
            throw new NestedTransactionNotSupportedException('Nested transaction manager scopes are not supported.');
        }

        $pdo = $this->connection->pdo();
        $this->begin($pdo);

        try {
            $result = $operation();
            $this->commit($pdo);

            return $result;
        } catch (Throwable $failure) {
            $this->rollback($pdo);
            throw $failure;
        } finally {
            $this->depth = 0;
            $this->scope = PdoTransactionScope::None;
        }
    }

    public function state(): TransactionState
    {
        return $this->state;
    }

    public function depth(): int
    {
        return $this->depth;
    }

    public function scope(): PdoTransactionScope
    {
        return $this->scope;
    }

    public function capabilities(): PersistenceCapabilities
    {
        $capabilities = [PersistenceCapability::Transactions];
        if ($this->connection->capabilities()->savepointsSupported()) {
            $capabilities[] = PersistenceCapability::Savepoints;
        }

        return PersistenceCapabilities::of($capabilities);
    }

    private function begin(PDO $pdo): void
    {
        try {
            if (!$pdo->inTransaction()) {
                if (!$pdo->beginTransaction()) {
                    throw new PdoTransactionException('PDO transaction could not be started.');
                }
                $this->scope = PdoTransactionScope::Owned;
            } else {
                if ($this->externalPolicy === PdoExternalTransactionPolicy::Reject) {
                    throw new PdoTransactionException('An external PDO transaction is already active.');
                }
                if (!$this->connection->capabilities()->savepointsSupported()) {
                    throw new PdoTransactionException('The active platform does not support savepoint participation.');
                }
                $pdo->exec('SAVEPOINT ' . $this->savepointName);
                $this->scope = PdoTransactionScope::Savepoint;
            }

            $this->depth = 1;
            $this->state = TransactionState::Active;
        } catch (PDOException $failure) {
            throw new PdoTransactionException('PDO transaction activation failed.', 0, $failure);
        }
    }

    private function commit(PDO $pdo): void
    {
        try {
            if ($this->scope === PdoTransactionScope::Owned) {
                if (!$pdo->commit()) {
                    throw new PdoTransactionException('PDO transaction could not be committed.');
                }
            } elseif ($this->scope === PdoTransactionScope::Savepoint) {
                $pdo->exec('RELEASE SAVEPOINT ' . $this->savepointName);
            }
            $this->state = TransactionState::Committed;
        } catch (PDOException $failure) {
            throw new PdoTransactionException('PDO transaction commit failed.', 0, $failure);
        }
    }

    private function rollback(PDO $pdo): void
    {
        try {
            if ($this->scope === PdoTransactionScope::Owned && $pdo->inTransaction()) {
                $pdo->rollBack();
            } elseif ($this->scope === PdoTransactionScope::Savepoint && $pdo->inTransaction()) {
                $pdo->exec('ROLLBACK TO SAVEPOINT ' . $this->savepointName);
            }
            $this->state = TransactionState::RolledBack;
        } catch (PDOException $failure) {
            throw new PdoTransactionException('PDO transaction rollback failed.', 0, $failure);
        }
    }
}
