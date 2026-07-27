<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Memory;

use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Exceptions\NestedTransactionNotSupportedException;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\TransactionState;
use Throwable;

final class InMemoryTransactionManager implements
    TransactionManagerInterface,
    PersistenceCapabilityProviderInterface
{
    private TransactionState $state = TransactionState::Idle;

    private int $depth = 0;

    /**
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     */
    public function transactional(callable $operation): mixed
    {
        if ($this->depth > 0) {
            throw new NestedTransactionNotSupportedException(
                'Nested transactions are not supported by the in-memory adapter.',
            );
        }

        $this->depth = 1;
        $this->state = TransactionState::Active;

        try {
            $result = $operation();
            $this->state = TransactionState::Committed;

            return $result;
        } catch (Throwable $failure) {
            $this->state = TransactionState::RolledBack;

            throw $failure;
        } finally {
            $this->depth = 0;
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

    public function capabilities(): PersistenceCapabilities
    {
        return PersistenceCapabilities::of([
            PersistenceCapability::Transactions,
        ]);
    }
}
