<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Composition;

use Sif\Foundation\Migration\Contracts\MigrationInstallationPlanProviderInterface;
use Sif\Foundation\Migration\Execution\MigrationExecutor;
use Sif\Foundation\Migration\Installer\MigrationMutationHandler;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryStore;
use Sif\Foundation\Migration\Pdo\Installer\PdoMigrationHistoryProvisioningHandler;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLock;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationCatalog;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationHandler;
use Sif\Foundation\Migration\Pdo\Transaction\PdoMigrationTransactionManager;
use Sif\Foundation\Migration\Registry\MigrationRegistry;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;
use Sif\Foundation\Migration\Selection\MigrationSelector;

final readonly class PdoMigrationAdapterFactory
{
    public function compose(
        PdoMigrationConnection $connection,
        MigrationRegistry $registry,
        PdoMigrationSqlOperationCatalog $operations,
        ?PdoMigrationAdapterOptions $options = null,
        ?MigrationInstallationPlanProviderInterface $installationPlans = null,
    ): PdoMigrationAdapterComposition {
        $options ??= new PdoMigrationAdapterOptions();

        $history = new PdoMigrationHistoryStore(
            $connection,
            $options->historyTable(),
            $options->autoInitializeHistory(),
        );
        $lock = new PdoMigrationLock($connection, $options->lockResource(), $options->lockTimeout());
        $transactions = new PdoMigrationTransactionManager(
            $connection,
            $options->externalTransactionPolicy(),
            $options->savepoint(),
        );
        $handler = new PdoMigrationSqlOperationHandler($connection, $operations);
        $executor = new MigrationExecutor([$handler], $history, $lock, $transactions);
        $runtime = new MigrationRuntime($registry, $history, new MigrationSelector(), $executor);
        $provisioning = new PdoMigrationHistoryProvisioningHandler($history, $options->historyTable());
        $migrationHandler = $installationPlans === null
            ? null
            : new MigrationMutationHandler($executor, $installationPlans);

        return new PdoMigrationAdapterComposition(
            $history,
            $lock,
            $transactions,
            $handler,
            $executor,
            $runtime,
            $provisioning,
            $migrationHandler,
        );
    }
}
