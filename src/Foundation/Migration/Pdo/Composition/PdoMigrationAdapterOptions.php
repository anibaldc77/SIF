<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Composition;

use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryTable;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLockResource;
use Sif\Foundation\Migration\Pdo\Lock\PdoMigrationLockTimeout;
use Sif\Foundation\Migration\Pdo\Transaction\PdoMigrationExternalTransactionPolicy;

final readonly class PdoMigrationAdapterOptions
{
    public function __construct(
        private PdoMigrationHistoryTable $historyTable = new PdoMigrationHistoryTable(),
        private PdoMigrationLockResource $lockResource = new PdoMigrationLockResource(),
        private PdoMigrationLockTimeout $lockTimeout = new PdoMigrationLockTimeout(),
        private PdoMigrationExternalTransactionPolicy $externalTransactionPolicy = new PdoMigrationExternalTransactionPolicy(),
        private string $savepoint = 'sif_migration',
        private bool $autoInitializeHistory = false,
    ) {
    }

    public function historyTable(): PdoMigrationHistoryTable { return $this->historyTable; }
    public function lockResource(): PdoMigrationLockResource { return $this->lockResource; }
    public function lockTimeout(): PdoMigrationLockTimeout { return $this->lockTimeout; }
    public function externalTransactionPolicy(): PdoMigrationExternalTransactionPolicy { return $this->externalTransactionPolicy; }
    public function savepoint(): string { return $this->savepoint; }
    public function autoInitializeHistory(): bool { return $this->autoInitializeHistory; }
}
