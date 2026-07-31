<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Installer;

use Sif\Foundation\Installer\Contracts\MutationHandlerInterface;
use Sif\Foundation\Installer\Execution\MutationExecutionResult;
use Sif\Foundation\Installer\Execution\MutationExecutionStatus;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryStore;
use Sif\Foundation\Migration\Pdo\History\PdoMigrationHistoryTable;

final readonly class PdoMigrationHistoryProvisioningHandler implements MutationHandlerInterface
{
    public const OPERATION = 'provision-migration-history';

    public function __construct(
        private PdoMigrationHistoryStore $historyStore,
        private PdoMigrationHistoryTable $table,
    ) {
    }

    public function supports(MutationDescriptor $mutation): bool
    {
        return $mutation->classification()->equals(MutationClassification::migration())
            && $mutation->operation() === self::OPERATION;
    }

    public function apply(MutationDescriptor $mutation): MutationExecutionResult
    {
        $this->historyStore->initialize();

        return new MutationExecutionResult(
            $mutation->identifier(),
            MutationExecutionStatus::applied(),
            hash('sha256', $this->table->logicalName()),
            ['history_table' => $this->table->logicalName()],
        );
    }

    public function compensate(MutationDescriptor $mutation, MutationExecutionResult $appliedResult): MutationExecutionResult
    {
        return new MutationExecutionResult(
            $mutation->identifier(),
            MutationExecutionStatus::compensationUnsupported(),
            $appliedResult->receiptFingerprint(),
            ['history_table' => $this->table->logicalName()],
        );
    }
}
