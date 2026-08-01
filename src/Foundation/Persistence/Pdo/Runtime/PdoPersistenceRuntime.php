<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Runtime;

use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Execution\PdoPreparedStatementExecutor;
use Sif\Foundation\Persistence\Pdo\Repository\PdoRepositoryRegistry;
use Sif\Foundation\Contracts\TransactionManagerInterface;

final readonly class PdoPersistenceRuntime
{
    public function __construct(
        private PdoPersistenceConnection $connection,
        private PdoRepositoryRegistry $repositories,
        private TransactionManagerInterface $transactions,
        private PdoPreparedStatementExecutor $executor,
    ) {
    }

    public function connection(): PdoPersistenceConnection { return $this->connection; }
    public function repositories(): PdoRepositoryRegistry { return $this->repositories; }
    public function transactions(): TransactionManagerInterface { return $this->transactions; }
    public function executor(): PdoPreparedStatementExecutor { return $this->executor; }

    /** @return array{connection: array<string, mixed>, repository_count: int} */
    public function summary(): array
    {
        return [
            'connection' => $this->connection->summary(),
            'repository_count' => $this->repositories->count(),
        ];
    }
}
