<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Composition;

use Sif\Foundation\Migration\Execution\MigrationExecutor;
use Sif\Foundation\Migration\Installer\MigrationMutationHandler;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryStore;
use Sif\Foundation\Migration\Pdo\Installer\PdoMigrationHistoryProvisioningHandler;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLock;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationHandler;
use Sif\Foundation\Migration\Pdo\Transaction\PdoMigrationTransactionManager;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;

final readonly class PdoMigrationAdapterComposition
{
    public function __construct(
        private PdoMigrationHistoryStore $historyStore,
        private PdoMigrationLock $lock,
        private PdoMigrationTransactionManager $transactions,
        private PdoMigrationSqlOperationHandler $sqlHandler,
        private MigrationExecutor $executor,
        private MigrationRuntime $runtime,
        private PdoMigrationHistoryProvisioningHandler $historyProvisioningHandler,
        private ?MigrationMutationHandler $migrationMutationHandler = null,
    ) {
    }

    public function historyStore(): PdoMigrationHistoryStore { return $this->historyStore; }
    public function lock(): PdoMigrationLock { return $this->lock; }
    public function transactions(): PdoMigrationTransactionManager { return $this->transactions; }
    public function sqlHandler(): PdoMigrationSqlOperationHandler { return $this->sqlHandler; }
    public function executor(): MigrationExecutor { return $this->executor; }
    public function runtime(): MigrationRuntime { return $this->runtime; }
    public function historyProvisioningHandler(): PdoMigrationHistoryProvisioningHandler { return $this->historyProvisioningHandler; }
    public function migrationMutationHandler(): ?MigrationMutationHandler { return $this->migrationMutationHandler; }
}
