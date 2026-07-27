<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Exceptions\NestedTransactionNotSupportedException;
use Sif\Foundation\Persistence\TransactionState;
use Throwable;

final class InMemoryTransactionManager implements TransactionManagerInterface
{
    private TransactionState $state = TransactionState::Idle;

    private int $depth = 0;

    /**
     * @var list<TransactionState>
     */
    private array $history = [TransactionState::Idle];

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
                'Nested transactions are not supported by this manager.',
            );
        }

        $this->depth = 1;
        $this->transition(TransactionState::Active);

        try {
            $result = $operation();
            $this->transition(TransactionState::Committed);

            return $result;
        } catch (Throwable $failure) {
            $this->transition(TransactionState::RolledBack);

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

    /**
     * @return list<TransactionState>
     */
    public function history(): array
    {
        return $this->history;
    }

    private function transition(TransactionState $state): void
    {
        $this->state = $state;
        $this->history[] = $state;
    }
}
