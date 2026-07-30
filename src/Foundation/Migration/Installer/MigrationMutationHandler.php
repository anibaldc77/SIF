<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Installer;

use Sif\Foundation\Installer\Contracts\MutationHandlerInterface;
use Sif\Foundation\Installer\Execution\MutationExecutionResult;
use Sif\Foundation\Installer\Execution\MutationExecutionStatus;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Migration\Contracts\MigrationInstallationPlanProviderInterface;
use Sif\Foundation\Migration\Execution\MigrationExecutor;

final readonly class MigrationMutationHandler implements MutationHandlerInterface
{
    public function __construct(
        private MigrationExecutor $executor,
        private MigrationInstallationPlanProviderInterface $plans,
    ) {}

    public function supports(MutationDescriptor $mutation): bool
    {
        return $mutation->classification()->equals(MutationClassification::migration())
            && $mutation->operation() === 'execute-migrations';
    }

    public function apply(MutationDescriptor $mutation): MutationExecutionResult
    {
        [$plan, $authorization] = $this->plans->applyPlan($mutation);
        $report = $this->executor->execute($plan, $authorization);
        if (!$report->successful()) {
            throw new \RuntimeException('Migration mutation execution failed.');
        }
        return new MutationExecutionResult(
            $mutation->identifier(),
            MutationExecutionStatus::applied(),
            $report->planFingerprint(),
            ['migration_count' => $report->completedCount()],
        );
    }

    public function compensate(MutationDescriptor $mutation, MutationExecutionResult $appliedResult): MutationExecutionResult
    {
        [$plan, $authorization] = $this->plans->compensationPlan($mutation);
        $report = $this->executor->execute($plan, $authorization);
        if (!$report->successful()) {
            throw new \RuntimeException('Migration mutation compensation failed.');
        }
        return new MutationExecutionResult(
            $mutation->identifier(),
            MutationExecutionStatus::compensated(),
            $report->planFingerprint(),
            ['migration_count' => $report->completedCount()],
        );
    }
}
