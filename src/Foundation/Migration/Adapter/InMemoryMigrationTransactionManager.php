<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Adapter;

use LogicException;
use Sif\Foundation\Migration\Contracts\MigrationTransactionManagerInterface;

final class InMemoryMigrationTransactionManager implements MigrationTransactionManagerInterface
{
    private bool $active = false;
    /** @var list<string> */
    private array $journal = [];

    public function __construct(private readonly bool $supported = true) {}

    public function supportsTransactions(): bool { return $this->supported; }
    public function begin(): void { if (!$this->supported || $this->active) throw new LogicException('Transaction cannot begin.'); $this->active = true; $this->journal[] = 'begin'; }
    public function commit(): void { if (!$this->active) throw new LogicException('No active transaction.'); $this->active = false; $this->journal[] = 'commit'; }
    public function rollBack(): void { if (!$this->active) throw new LogicException('No active transaction.'); $this->active = false; $this->journal[] = 'rollback'; }
    public function active(): bool { return $this->active; }
    /** @return list<string> */ public function journal(): array { return $this->journal; }
}
