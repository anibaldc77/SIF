<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\UnitOfWork;

use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Persistence\ChangeSet;
use Sif\Foundation\Persistence\InMemoryUnitOfWork;
use Sif\Foundation\Persistence\Pdo\Repository\PdoRepositoryRegistry;

final class PdoUnitOfWork extends InMemoryUnitOfWork
{
    public function __construct(
        TransactionManagerInterface $transactionManager,
        private readonly PdoRepositoryRegistry $repositories,
    ) {
        parent::__construct($transactionManager);
    }

    protected function apply(ChangeSet $changes): void
    {
        foreach ($changes->newObjects() as $object) {
            $this->repositories->for($object)->saveObject($object);
        }

        foreach ($changes->dirtyObjects() as $object) {
            $this->repositories->for($object)->saveObject($object);
        }

        foreach ($changes->removedObjects() as $object) {
            $this->repositories->for($object)->removeObject($object);
        }
    }
}
