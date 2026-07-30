<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Runtime;

use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\Contracts\MigrationHistoryStoreInterface;
use Sif\Foundation\Migration\Execution\MigrationExecutionReport;
use Sif\Foundation\Migration\Execution\MigrationExecutor;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationIntegrityChecker;
use Sif\Foundation\Migration\History\MigrationIntegrityReport;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\Registry\MigrationRegistry;
use Sif\Foundation\Migration\Selection\MigrationDryRunReport;
use Sif\Foundation\Migration\Selection\MigrationExecutionPlan;
use Sif\Foundation\Migration\Selection\MigrationSelector;

final readonly class MigrationRuntime
{
    public function __construct(
        private MigrationRegistry $registry,
        private MigrationHistoryStoreInterface $historyStore,
        private MigrationSelector $selector,
        private MigrationExecutor $executor,
        private MigrationIntegrityChecker $integrityChecker = new MigrationIntegrityChecker(),
    ) {
    }

    public function registry(): MigrationRegistry
    {
        return $this->registry;
    }

    public function history(): MigrationHistory
    {
        return $this->historyStore->history();
    }

    public function inspect(): MigrationIntegrityReport
    {
        return $this->integrityChecker->inspect($this->registry, $this->history());
    }

    public function plan(MigrationRequest $request): MigrationExecutionPlan
    {
        return $this->selector->select($this->registry, $this->history(), $request);
    }

    public function dryRun(MigrationRequest $request): MigrationDryRunReport
    {
        return new MigrationDryRunReport($this->plan($request));
    }

    public function execute(
        MigrationExecutionPlan $plan,
        MigrationExecutionAuthorization $authorization,
    ): MigrationExecutionReport {
        return $this->executor->execute($plan, $authorization);
    }
}
