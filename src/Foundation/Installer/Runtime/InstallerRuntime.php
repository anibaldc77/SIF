<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Runtime;

use Sif\Foundation\Installer\Contracts\InstallationStepInterface;
use Sif\Foundation\Installer\Contracts\RequirementProbeInterface;
use Sif\Foundation\Installer\Execution\InstallationExecutionReport;
use Sif\Foundation\Installer\ExecutionAuthorization;
use Sif\Foundation\Installer\InstallationDryRunReport;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\InstallationStepPlan;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\Orchestration\InstallerOrchestrator;
use Sif\Foundation\Installer\RequirementAssessmentReport;
use Sif\Foundation\Installer\Requirements\RequirementAssessor;
use Sif\Foundation\Installer\Steps\InstallationStepPlanner;

final readonly class InstallerRuntime
{
    /** @var list<RequirementProbeInterface> */
    private array $probes;

    /** @var list<InstallationStepInterface> */
    private array $steps;

    /**
     * @param iterable<RequirementProbeInterface> $probes
     * @param iterable<InstallationStepInterface> $steps
     */
    public function __construct(
        private RequirementAssessor $requirementAssessor,
        private InstallationStepPlanner $stepPlanner,
        private InstallerOrchestrator $orchestrator,
        iterable $probes = [],
        iterable $steps = [],
    ) {
        $this->probes = $this->normalizeProbes($probes);
        $this->steps = $this->normalizeSteps($steps);
    }

    public function assess(InstallationRequest $request): RequirementAssessmentReport
    {
        return $this->requirementAssessor->assess($request, $this->probes);
    }

    public function planSteps(): InstallationStepPlan
    {
        return $this->stepPlanner->compile($this->steps);
    }

    public function dryRun(InstallationRequest $request, MutationPlan $mutations): InstallationDryRunReport
    {
        return $this->orchestrator->dryRun(
            $request,
            $this->assess($request),
            $this->planSteps(),
            $mutations,
        );
    }

    public function execute(
        InstallationDryRunReport $dryRun,
        ExecutionAuthorization $authorization,
    ): InstallationExecutionReport {
        return $this->orchestrator->execute(
            $dryRun->request(),
            $dryRun->requirements(),
            $dryRun->mutations(),
            $authorization,
        );
    }

    /** @return list<RequirementProbeInterface> */
    public function probes(): array
    {
        return $this->probes;
    }

    /** @return list<InstallationStepInterface> */
    public function steps(): array
    {
        return $this->steps;
    }

    /** @param iterable<RequirementProbeInterface> $probes
     *  @return list<RequirementProbeInterface>
     */
    private function normalizeProbes(iterable $probes): array
    {
        $normalized = [];
        foreach ($probes as $probe) {
            $normalized[] = $probe;
        }

        return $normalized;
    }

    /** @param iterable<InstallationStepInterface> $steps
     *  @return list<InstallationStepInterface>
     */
    private function normalizeSteps(iterable $steps): array
    {
        $normalized = [];
        foreach ($steps as $step) {
            $normalized[] = $step;
        }

        return $normalized;
    }
}
