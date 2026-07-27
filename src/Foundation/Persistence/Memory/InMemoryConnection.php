<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Memory;

use Sif\Foundation\Contracts\ConnectionInterface;
use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\PersistenceCapability;

final class InMemoryConnection implements
    ConnectionInterface,
    PersistenceCapabilityProviderInterface
{
    private bool $open = true;

    public function __construct(
        private readonly ConnectionName $name,
    ) {
    }

    public function name(): ConnectionName
    {
        return $this->name;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function reopen(): void
    {
        $this->open = true;
    }

    public function capabilities(): PersistenceCapabilities
    {
        return PersistenceCapabilities::of([
            PersistenceCapability::Transactions,
            PersistenceCapability::QueryCriteria,
            PersistenceCapability::Sorting,
            PersistenceCapability::OffsetPagination,
            PersistenceCapability::Projection,
            PersistenceCapability::UnitOfWork,
        ]);
    }
}
