<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Orchestration;

use Sif\Foundation\Installer\Execution\InstallationExecutionReport;
use Sif\Foundation\Installer\Execution\MutationPlanExecutor;
use Sif\Foundation\Installer\ExecutionAuthorization;
use Sif\Foundation\Installer\Exceptions\InstallationAuthorizationMismatchException;
use Sif\Foundation\Installer\Exceptions\InstallationRequirementsNotSatisfiedException;
use Sif\Foundation\Installer\InstallationDryRunReport;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\InstallationStepPlan;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\RequirementAssessmentReport;

final readonly class InstallerOrchestrator
{
    public function __construct(private MutationPlanExecutor $executor)
    {
    }

    public function dryRun(
        InstallationRequest $request,
        RequirementAssessmentReport $requirements,
        InstallationStepPlan $steps,
        MutationPlan $mutations,
    ): InstallationDryRunReport {
        return new InstallationDryRunReport($request, $requirements, $steps, $mutations);
    }

    public function execute(
        InstallationRequest $request,
        RequirementAssessmentReport $requirements,
        MutationPlan $mutations,
        ExecutionAuthorization $authorization,
    ): InstallationExecutionReport {
        if (!$requirements->canProceed()) {
            throw new InstallationRequirementsNotSatisfiedException(
                'Installation execution is forbidden while required requirements are unsatisfied.',
            );
        }

        if (
            !$authorization->mutationAllowed()
            || !$authorization->installationIdentifier()->equals($request->identifier())
            || $authorization->planFingerprint() !== $mutations->fingerprint()
        ) {
            throw new InstallationAuthorizationMismatchException(
                'Execution authorization does not match the installation request and compiled mutation plan.',
            );
        }

        return $this->executor->execute($mutations);
    }
}
