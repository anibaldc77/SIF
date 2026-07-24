<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\BootResult;
use Sif\Foundation\BootStage;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\BootstrapInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\DTO\BootError;
use Sif\Foundation\DTO\BootWarning;
use Sif\Foundation\Environment;
use Sif\Foundation\Exceptions\InvalidBootResultException;
use Sif\Foundation\Exceptions\InvalidRuntimeTransitionException;
use Sif\Foundation\Framework;
use Sif\Foundation\Lifecycle;
use Sif\Foundation\Runtime;
use Sif\Foundation\RuntimeState;
use Sif\Foundation\ServiceProviderCollection;

final class LifecycleOrchestrationTest extends TestCase
{
    public function testBootStageContainsApprovedValues(): void
    {
        self::assertSame(
            [
                'created',
                'environment',
                'bootstrap',
                'providers',
                'booted',
                'running',
                'shutdown',
                'failed',
            ],
            array_column(BootStage::cases(), 'value'),
        );
    }

    public function testEnvironmentFactoriesAndComparison(): void
    {
        $environment = Environment::testing();

        self::assertTrue($environment->isTesting());
        self::assertTrue($environment->equals(Environment::testing()));
        self::assertFalse($environment->equals(Environment::production()));
        self::assertSame('custom', (string) Environment::custom('custom'));
    }

    public function testRuntimeValidLifecycle(): void
    {
        $runtime = new Runtime();
        $runtime->transitionTo(
            RuntimeState::Bootstrapping,
            BootStage::Bootstrap,
        );
        $runtime->transitionTo(RuntimeState::Booted, BootStage::Booted);
        $runtime->transitionTo(RuntimeState::Running, BootStage::Running);
        $runtime->transitionTo(RuntimeState::Stopping, BootStage::Shutdown);
        $runtime->transitionTo(RuntimeState::Stopped, BootStage::Shutdown);

        self::assertTrue($runtime->isStopped());
        self::assertNotNull($runtime->startedAt());
        self::assertNotNull($runtime->stoppedAt());
    }

    public function testRuntimeAllowsShutdownDirectlyAfterBoot(): void
    {
        $runtime = new Runtime();
        $runtime->transitionTo(
            RuntimeState::Bootstrapping,
            BootStage::Bootstrap,
        );
        $runtime->transitionTo(RuntimeState::Booted, BootStage::Booted);
        $runtime->transitionTo(RuntimeState::Stopping, BootStage::Shutdown);
        $runtime->transitionTo(RuntimeState::Stopped, BootStage::Shutdown);

        self::assertTrue($runtime->isStopped());
    }

    public function testRuntimeRejectsInvalidTransition(): void
    {
        $this->expectException(InvalidRuntimeTransitionException::class);

        (new Runtime())->transitionTo(
            RuntimeState::Running,
            BootStage::Running,
        );
    }

    public function testRuntimeRetainsFailure(): void
    {
        $runtime = new Runtime();
        $cause = new \RuntimeException('broken');

        $runtime->fail($cause, BootStage::Failed);

        self::assertTrue($runtime->hasFailed());
        self::assertSame($cause, $runtime->failure());
    }

    public function testLifecycleReturnsResultsWithoutChangingRuntimeState(): void
    {
        $application = Framework::create();
        $lifecycle = new Lifecycle();
        $providers = new ServiceProviderCollection();

        $bootResult = $lifecycle->boot($application, $providers);
        $shutdownResult = $lifecycle->shutdown($application, $providers);

        self::assertTrue($bootResult->succeeded());
        self::assertTrue($shutdownResult->succeeded());
        self::assertTrue($application->runtime()->isCreated());
    }

    public function testKernelCanShutdownApplicationImmediatelyAfterBoot(): void
    {
        $application = Framework::create();

        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->runtime()->isBooted());
        self::assertTrue($application->shutdown()->succeeded());
        self::assertTrue($application->runtime()->isStopped());
    }

    public function testBootResultAndDtosAreStructured(): void
    {
        $start = new DateTimeImmutable('2026-01-01T00:00:00.000000Z');
        $end = new DateTimeImmutable('2026-01-01T00:00:00.250000Z');
        $warning = new BootWarning(
            'warning',
            'Careful',
            BootStage::Bootstrap,
        );
        $success = BootResult::success(
            BootStage::Booted,
            $start,
            $end,
            [$warning],
        );

        self::assertTrue($success->succeeded());
        self::assertSame(250.0, $success->durationMilliseconds());
        self::assertSame([$warning], $success->warnings());

        $error = new BootError('error', 'Broken', BootStage::Failed);
        $cause = new \RuntimeException('Broken');
        $failure = BootResult::failure(
            BootStage::Failed,
            $start,
            $end,
            [$error],
            $cause,
        );

        self::assertTrue($failure->failed());
        self::assertSame($cause, $failure->cause());
        self::assertSame('error', $error->jsonSerialize()['code']);
    }

    public function testFailureAcceptsOneError(): void
    {
        $now = new DateTimeImmutable();
        $error = new BootError('one', 'One error', BootStage::Failed);
        $result = BootResult::failure(
            BootStage::Failed,
            $now,
            $now,
            [$error],
        );

        self::assertSame([$error], $result->errors());
    }

    public function testFailureAcceptsMultipleErrors(): void
    {
        $now = new DateTimeImmutable();
        $errors = [
            new BootError('one', 'First', BootStage::Failed),
            new BootError('two', 'Second', BootStage::Failed),
        ];
        $result = BootResult::failure(
            BootStage::Failed,
            $now,
            $now,
            $errors,
        );

        self::assertSame($errors, $result->errors());
        self::assertCount(2, $result->errors());
    }

    public function testFailureAcceptsWarnings(): void
    {
        $now = new DateTimeImmutable();
        $error = new BootError('error', 'Broken', BootStage::Failed);
        $warnings = [
            new BootWarning('warning', 'Careful', BootStage::Failed),
        ];
        $result = BootResult::failure(
            BootStage::Failed,
            $now,
            $now,
            [$error],
            null,
            $warnings,
        );

        self::assertSame($warnings, $result->warnings());
    }

    public function testFailureRejectsEmptyErrors(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvalidBootResultException::class);

        BootResult::failure(BootStage::Failed, $now, $now, []);
    }

    public function testBootstrapCreatesIndependentGraphsAndRunsLifecycle(): void
    {
        $first = Framework::create(Environment::testing());
        $second = Framework::create(Environment::testing());

        self::assertNotSame($first, $second);
        self::assertNotSame($first->runtime(), $second->runtime());
        self::assertTrue($first->run()->succeeded());
        self::assertTrue($first->runtime()->isRunning());
        self::assertTrue($first->shutdown()->succeeded());
        self::assertTrue($first->runtime()->isStopped());
        self::assertTrue($second->runtime()->isCreated());
    }

    public function testFrameworkUsesCustomBootstrap(): void
    {
        $expected = Framework::create();
        $bootstrap = new class($expected) implements BootstrapInterface {
            public function __construct(
                private ApplicationInterface $application,
            ) {
            }

            public function createApplication(
                EnvironmentInterface $environment,
            ): ApplicationInterface {
                return $this->application;
            }
        };

        self::assertSame(
            $expected,
            Framework::create(Environment::development(), $bootstrap),
        );
        self::assertSame('2.0.0-alpha1', Framework::version());
    }

    public function testRepeatedRunBecomesFailureResult(): void
    {
        $application = Framework::create();

        self::assertTrue($application->run()->succeeded());

        $result = $application->run();

        self::assertTrue($result->failed());
        self::assertTrue($application->runtime()->hasFailed());
        self::assertCount(1, $result->errors());
    }
}
