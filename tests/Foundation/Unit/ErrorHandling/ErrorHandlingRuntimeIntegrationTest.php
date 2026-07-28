<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Environment;
use Sif\Foundation\ErrorHandling\Classification\OrderedThrowableClassifier;
use Sif\Foundation\ErrorHandling\Clock\FrozenFailureClock;
use Sif\Foundation\ErrorHandling\Factory\FailureEnvelopeFactory;
use Sif\Foundation\ErrorHandling\Factory\FixedFailureIdGenerator;
use Sif\Foundation\ErrorHandling\FailureId;
use Sif\Foundation\ErrorHandling\Metadata\SafeFailureMetadataNormalizer;
use Sif\Foundation\ErrorHandling\Planning\ErrorHandlingPlan;
use Sif\Foundation\ErrorHandling\Recovery\OrderedRecoveryDecider;
use Sif\Foundation\ErrorHandling\Reporting\AcceptAllFailureReportFilter;
use Sif\Foundation\ErrorHandling\Reporting\FailureReportRoute;
use Sif\Foundation\ErrorHandling\Reporting\FailureReporterDispatcher;
use Sif\Foundation\ErrorHandling\Reporting\InMemoryFailureReporter;
use Sif\Foundation\ErrorHandling\Reporting\NullEmergencyFailureReporter;
use Sif\Foundation\ErrorHandling\Runtime\RuntimeErrorHandlingServiceProvider;
use Sif\Foundation\ServiceProvider;

final class ErrorHandlingRuntimeIntegrationTest extends TestCase
{
    public function testBootstrapWithoutPlanRemainsCompatible(): void
    {
        $application = (new Bootstrap())->createApplication(Environment::testing());

        self::assertNull($application->errorHandler());
        self::assertNull($application->lastErrorHandlingResult());
        self::assertFalse($application->hasCapability('error-handling'));
        self::assertFalse($application->providers()->has(RuntimeErrorHandlingServiceProvider::class));
    }

    public function testBootstrapPublishesProviderAndCapabilityWhenConfigured(): void
    {
        [$plan] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());

        self::assertNotNull($application->errorHandler());
        self::assertTrue($application->providers()->has(RuntimeErrorHandlingServiceProvider::class));
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->hasCapability('error-handling'));
    }

    public function testRegisterFailureIsHandledWithoutChangingBootResult(): void
    {
        [$plan, $reporter] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());
        $application->providers()->add($this->failingRegisterProvider());

        $result = $application->boot();

        self::assertTrue($result->failed());
        self::assertInstanceOf(RuntimeException::class, $result->cause());
        self::assertSame('register failed', $result->cause()->getMessage());
        self::assertNotNull($application->lastErrorHandlingResult());
        self::assertSame($result->cause(), $application->lastErrorHandlingResult()->envelope()->throwable());
        self::assertSame(1, $reporter->count());
    }

    public function testBootFailureMetadataContainsLifecycleState(): void
    {
        [$plan] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());
        $application->providers()->add($this->failingRegisterProvider());
        $application->boot();

        $metadata = $application->lastErrorHandlingResult()?->envelope()->metadata();

        self::assertSame('failed', $metadata['boot_stage'] ?? null);
        self::assertSame(1, $metadata['error_count'] ?? null);
        self::assertSame('failed', $metadata['runtime_state'] ?? null);
        self::assertSame('runtime.boot', $application->lastErrorHandlingResult()?->envelope()->origin()->value());
    }

    public function testSuccessfulLifecycleDoesNotCreateFailureResult(): void
    {
        [$plan, $reporter] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());

        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->run()->succeeded());
        self::assertTrue($application->shutdown()->succeeded());
        self::assertNull($application->lastErrorHandlingResult());
        self::assertSame(0, $reporter->count());
    }

    public function testShutdownFailureIsObservedAfterRemainingProvidersRun(): void
    {
        [$plan, $reporter] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());
        $application->providers()->add($this->failingShutdownProvider());
        self::assertTrue($application->boot()->succeeded());

        $result = $application->shutdown();

        self::assertTrue($result->failed());
        self::assertNotNull($result->cause());
        self::assertSame('shutdown failed', $result->cause()->getMessage());
        self::assertSame('runtime.shutdown', $application->lastErrorHandlingResult()?->envelope()->origin()->value());
        self::assertSame(1, $reporter->count());
    }

    public function testConfiguredHandlerIdentityIsStable(): void
    {
        [$plan] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());
        $handler = $application->errorHandler();

        self::assertNotNull($handler);
        self::assertSame($handler, $application->errorHandler());
        self::assertTrue($application->boot()->succeeded());
        self::assertSame($handler, $application->errorHandler());
    }

    public function testLoggingAndErrorHandlingCanBeConfiguredIndependently(): void
    {
        [$plan] = $this->plan();
        $application = (new Bootstrap(errorHandlingPlan: $plan))->createApplication(Environment::testing());

        self::assertNull($application->logger());
        self::assertNotNull($application->errorHandler());
        self::assertFalse($application->hasCapability('logging'));
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->hasCapability('error-handling'));
    }

    /** @return array{ErrorHandlingPlan, InMemoryFailureReporter} */
    private function plan(): array
    {
        $reporter = new InMemoryFailureReporter();
        $plan = new ErrorHandlingPlan(
            OrderedThrowableClassifier::withUnknownFallback([]),
            new FailureEnvelopeFactory(
                new FixedFailureIdGenerator(new FailureId('failure-runtime')),
                new FrozenFailureClock(new DateTimeImmutable('2026-07-28T21:30:00+00:00')),
                new SafeFailureMetadataNormalizer(),
            ),
            OrderedRecoveryDecider::withRethrowFallback([]),
            new FailureReporterDispatcher([
                new FailureReportRoute('memory', new AcceptAllFailureReportFilter(), $reporter),
            ], new NullEmergencyFailureReporter()),
        );

        return [$plan, $reporter];
    }

    private function failingRegisterProvider(): ServiceProvider
    {
        return new class extends ServiceProvider {
            public function register(ApplicationInterface $application): void
            {
                throw new RuntimeException('register failed');
            }
        };
    }

    private function failingShutdownProvider(): ServiceProvider
    {
        return new class extends ServiceProvider {
            public function register(ApplicationInterface $application): void
            {
            }

            public function shutdown(ApplicationInterface $application): void
            {
                throw new RuntimeException('shutdown failed');
            }
        };
    }
}
