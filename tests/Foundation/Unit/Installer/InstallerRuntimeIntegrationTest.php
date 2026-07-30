<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Installer;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Environment;
use Sif\Foundation\Installer\Execution\MutationPlanExecutor;
use Sif\Foundation\Installer\InstallationIdentifier;
use Sif\Foundation\Installer\InstallationMode;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\Orchestration\InstallerOrchestrator;
use Sif\Foundation\Installer\Requirements\RequirementAssessor;
use Sif\Foundation\Installer\Runtime\InstallerRuntime;
use Sif\Foundation\Installer\Runtime\RuntimeInstallerServiceProvider;
use Sif\Foundation\Installer\Steps\InstallationStepPlanner;

final class InstallerRuntimeIntegrationTest extends TestCase
{
    public function testBootstrapWithoutInstallerRemainsCompatible(): void
    {
        $application = (new Bootstrap())->createApplication(Environment::testing());

        self::assertNull($application->installer());
        self::assertFalse($application->providers()->has(RuntimeInstallerServiceProvider::class));
        self::assertFalse($application->hasCapability('installer'));
    }

    public function testBootstrapPublishesInstallerWhenConfigured(): void
    {
        $installer = $this->installer();
        $application = (new Bootstrap(installer: $installer))->createApplication(Environment::testing());

        self::assertSame($installer, $application->installer());
        self::assertTrue($application->providers()->has(RuntimeInstallerServiceProvider::class));
        self::assertFalse($application->hasCapability('installer'));
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->hasCapability('installer'));
    }

    public function testRuntimeProducesDeterministicEmptyDryRun(): void
    {
        $request = new InstallationRequest(new InstallationIdentifier('runtime-test'), InstallationMode::fresh());
        $dryRun = $this->installer()->dryRun($request, new MutationPlan([]));

        self::assertTrue($dryRun->executable());
        self::assertSame([], $dryRun->requirements()->results());
        self::assertSame([], $dryRun->steps()->steps());
        self::assertSame([], $dryRun->mutations()->mutations());
        self::assertSame($dryRun->planFingerprint(), $dryRun->mutations()->fingerprint());
    }

    private function installer(): InstallerRuntime
    {
        return new InstallerRuntime(
            new RequirementAssessor(),
            new InstallationStepPlanner(),
            new InstallerOrchestrator(new MutationPlanExecutor([])),
        );
    }
}
