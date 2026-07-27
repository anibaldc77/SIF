<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Contracts\UnitOfWorkInterface;
use Sif\Foundation\Exceptions\InvalidUnitOfWorkTransitionException;
use Throwable;

class InMemoryUnitOfWork implements UnitOfWorkInterface
{
    /**
     * @var array<int, object>
     */
    private array $new = [];

    /**
     * @var array<int, object>
     */
    private array $dirty = [];

    /**
     * @var array<int, object>
     */
    private array $removed = [];

    private UnitOfWorkState $state = UnitOfWorkState::Clean;

    public function __construct(
        private readonly TransactionManagerInterface $transactionManager,
    ) {
    }

    public function registerNew(object $object): void
    {
        $id = spl_object_id($object);

        unset($this->dirty[$id], $this->removed[$id]);
        $this->new[$id] = $object;
        $this->state = UnitOfWorkState::Pending;
    }

    public function registerDirty(object $object): void
    {
        $id = spl_object_id($object);

        if (isset($this->new[$id]) || isset($this->removed[$id])) {
            return;
        }

        $this->dirty[$id] = $object;
        $this->state = UnitOfWorkState::Pending;
    }

    public function registerRemoved(object $object): void
    {
        $id = spl_object_id($object);

        unset($this->new[$id], $this->dirty[$id]);
        $this->removed[$id] = $object;
        $this->state = UnitOfWorkState::Pending;
    }

    public function commit(): void
    {
        if ($this->state === UnitOfWorkState::Committing) {
            throw new InvalidUnitOfWorkTransitionException(
                'Unit of work is already committing.',
            );
        }

        if ($this->isEmpty()) {
            $this->state = UnitOfWorkState::Committed;

            return;
        }

        $this->state = UnitOfWorkState::Committing;

        try {
            $this->transactionManager->transactional(
                function (): void {
                    $this->apply($this->changes());
                },
            );

            $this->clearTrackedObjects();
            $this->state = UnitOfWorkState::Committed;
        } catch (Throwable $failure) {
            $this->state = UnitOfWorkState::Failed;

            throw $failure;
        }
    }

    public function clear(): void
    {
        $this->clearTrackedObjects();
        $this->state = UnitOfWorkState::Clean;
    }

    public function state(): UnitOfWorkState
    {
        return $this->state;
    }

    public function isEmpty(): bool
    {
        return $this->new === []
            && $this->dirty === []
            && $this->removed === [];
    }

    public function changes(): ChangeSet
    {
        return new ChangeSet(
            new: array_values($this->new),
            dirty: array_values($this->dirty),
            removed: array_values($this->removed),
        );
    }

    protected function apply(ChangeSet $changes): void
    {
        // Reference coordination hook. Production adapters override or compose this.
    }

    private function clearTrackedObjects(): void
    {
        $this->new = [];
        $this->dirty = [];
        $this->removed = [];
    }
}
