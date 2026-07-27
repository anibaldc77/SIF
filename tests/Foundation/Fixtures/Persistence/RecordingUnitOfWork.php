<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Persistence\ChangeSet;
use Sif\Foundation\Persistence\InMemoryUnitOfWork;

final class RecordingUnitOfWork extends InMemoryUnitOfWork
{
    /**
     * @var list<ChangeSet>
     */
    private array $applied = [];

    public function __construct(
        TransactionManagerInterface $transactionManager,
    ) {
        parent::__construct($transactionManager);
    }

    /**
     * @return list<ChangeSet>
     */
    public function applied(): array
    {
        return $this->applied;
    }

    protected function apply(ChangeSet $changes): void
    {
        $this->applied[] = $changes;
    }
}
