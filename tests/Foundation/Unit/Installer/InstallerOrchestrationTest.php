<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Installer;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Installer\Contracts\MutationHandlerInterface;
use Sif\Foundation\Installer\Execution\MutationExecutionResult;
use Sif\Foundation\Installer\Execution\MutationExecutionStatus;
use Sif\Foundation\Installer\Execution\MutationPlanExecutor;
use Sif\Foundation\Installer\ExecutionAuthorization;
use Sif\Foundation\Installer\Exceptions\InstallationAuthorizationMismatchException;
use Sif\Foundation\Installer\Exceptions\InstallationRequirementsNotSatisfiedException;
use Sif\Foundation\Installer\InstallationIdentifier;
use Sif\Foundation\Installer\InstallationMode;
use Sif\Foundation\Installer\InstallationOption;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\InstallationStepPlan;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\Orchestration\InstallerOrchestrator;
use Sif\Foundation\Installer\OverwritePolicy;
use Sif\Foundation\Installer\RequirementAssessmentReport;
use Sif\Foundation\Installer\RequirementIdentifier;
use Sif\Foundation\Installer\RequirementProbeResult;
use Sif\Foundation\Installer\RequirementSeverity;
use Sif\Foundation\Installer\RollbackPolicy;

final class InstallerOrchestrationTest extends TestCase
{
    public function testDryRunIsDeterministicAndRedactsSensitiveOptions(): void
    {
        $request = new InstallationRequest(
            new InstallationIdentifier('install-1'),
            InstallationMode::fresh(),
            [new InstallationOption('database.password', 'secret', true)],
        );
        $plan = $this->plan();
        $orchestrator = new InstallerOrchestrator(new MutationPlanExecutor([$this->handler()]));

        $first = $orchestrator->dryRun($request, new RequirementAssessmentReport([]), new InstallationStepPlan([]), $plan);
        $second = $orchestrator->dryRun($request, new RequirementAssessmentReport([]), new InstallationStepPlan([]), $plan);

        self::assertTrue($first->executable());
        self::assertSame($plan->fingerprint(), $first->planFingerprint());
        self::assertSame($first->summary(), $second->summary());
        self::assertSame('[REDACTED]', $first->summary()['request']['options'][0]['value']);
    }

    public function testExecutionRequiresSatisfiedRequirements(): void
    {
        $plan = $this->plan();
        $request = $this->request();
        $requirements = new RequirementAssessmentReport([
            RequirementProbeResult::failed(new RequirementIdentifier('php.version'), RequirementSeverity::Required, 'PHP version is not supported.'),
        ]);
        $authorization = new ExecutionAuthorization('auth-1', $request->identifier(), $plan->fingerprint(), true);

        $this->expectException(InstallationRequirementsNotSatisfiedException::class);
        (new InstallerOrchestrator(new MutationPlanExecutor([$this->handler()])))
            ->execute($request, $requirements, $plan, $authorization);
    }

    public function testExecutionRejectsMismatchedAuthorization(): void
    {
        $plan = $this->plan();
        $request = $this->request();
        $authorization = new ExecutionAuthorization('auth-1', $request->identifier(), str_repeat('a', 64), true);

        $this->expectException(InstallationAuthorizationMismatchException::class);
        (new InstallerOrchestrator(new MutationPlanExecutor([$this->handler()])))
            ->execute($request, new RequirementAssessmentReport([]), $plan, $authorization);
    }

    public function testExecutionRejectsNonMutatingAuthorization(): void
    {
        $plan = $this->plan();
        $request = $this->request();
        $authorization = new ExecutionAuthorization('auth-1', $request->identifier(), $plan->fingerprint(), false);

        $this->expectException(InstallationAuthorizationMismatchException::class);
        (new InstallerOrchestrator(new MutationPlanExecutor([$this->handler()])))
            ->execute($request, new RequirementAssessmentReport([]), $plan, $authorization);
    }

    public function testMatchingAuthorizationExecutesCompiledPlan(): void
    {
        $plan = $this->plan();
        $request = $this->request();
        $authorization = new ExecutionAuthorization('auth-1', $request->identifier(), $plan->fingerprint(), true);

        $report = (new InstallerOrchestrator(new MutationPlanExecutor([$this->handler()])))
            ->execute($request, new RequirementAssessmentReport([]), $plan, $authorization);

        self::assertTrue($report->isSuccessful());
        self::assertSame($plan->fingerprint(), $report->planFingerprint());
        self::assertCount(1, $report->journal()->entries());
    }

    private function request(): InstallationRequest
    {
        return new InstallationRequest(new InstallationIdentifier('install-1'), InstallationMode::fresh());
    }

    private function plan(): MutationPlan
    {
        return new MutationPlan([
            new MutationDescriptor(
                'write-config',
                'persist',
                MutationClassification::configuration(),
                null,
                OverwritePolicy::deny(),
                RollbackPolicy::compensating(),
            ),
        ]);
    }

    private function handler(): MutationHandlerInterface
    {
        return new class implements MutationHandlerInterface {
            public function supports(MutationDescriptor $mutation): bool { return true; }
            public function apply(MutationDescriptor $mutation): MutationExecutionResult
            {
                return new MutationExecutionResult($mutation->identifier(), MutationExecutionStatus::applied());
            }
            public function compensate(MutationDescriptor $mutation, MutationExecutionResult $appliedResult): MutationExecutionResult
            {
                return new MutationExecutionResult($mutation->identifier(), MutationExecutionStatus::compensated());
            }
        };
    }
}
