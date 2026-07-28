<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\ErrorHandling\Classification\OrderedThrowableClassifier;
use Sif\Foundation\ErrorHandling\Clock\FrozenFailureClock;
use Sif\Foundation\ErrorHandling\Factory\FailureEnvelopeFactory;
use Sif\Foundation\ErrorHandling\Factory\FixedFailureIdGenerator;
use Sif\Foundation\ErrorHandling\FailureId;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Metadata\SafeFailureMetadataNormalizer;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandler;
use Sif\Foundation\ErrorHandling\Planning\ErrorHandlingPlan;
use Sif\Foundation\ErrorHandling\Recovery\OrderedRecoveryDecider;
use Sif\Foundation\ErrorHandling\Reporting\AcceptAllFailureReportFilter;
use Sif\Foundation\ErrorHandling\Reporting\FailureReportRoute;
use Sif\Foundation\ErrorHandling\Reporting\FailureReporterDispatcher;
use Sif\Foundation\ErrorHandling\Reporting\InMemoryFailureReporter;
use Sif\Foundation\ErrorHandling\Reporting\NullEmergencyFailureReporter;

final class ErrorHandlingOrchestrationTest extends TestCase
{
    public function testPlanPreservesConfiguredCollaborators(): void
    {
        [$handler, $plan] = $this->fixture();
        self::assertSame($plan, $handler->plan());
        self::assertSame($plan->classifier(), $handler->plan()->classifier());
    }

    public function testHandleComposesAllStages(): void
    {
        [$handler] = $this->fixture();
        $throwable = new RuntimeException('boom');
        $result = $handler->handle($throwable, new FailureOrigin('runtime.test'), ['token' => 'x']);
        self::assertSame($throwable, $result->envelope()->throwable());
        self::assertSame('unknown', $result->classification()->category()->value());
        self::assertSame('rethrow', $result->recoveryDecision()->action()->value());
        self::assertSame(['memory'], $result->reportingResult()->reportedRoutes());
    }

    public function testEnvelopeUsesClassificationValues(): void
    {
        [$handler] = $this->fixture();
        $result = $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'));
        self::assertSame($result->classification()->category()->value(), $result->envelope()->category()->value());
        self::assertSame($result->classification()->severity()->value(), $result->envelope()->severity()->value());
        self::assertSame($result->classification()->disposition()->value(), $result->envelope()->disposition()->value());
    }

    public function testAttemptIsForwardedToRecoveryDecider(): void
    {
        [$handler] = $this->fixture();
        $result = $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'), [], 3);
        self::assertSame('fallback.rethrow', $result->recoveryDecision()->policy());
    }

    public function testAttemptMustBePositive(): void
    {
        [$handler] = $this->fixture();
        $this->expectException(InvalidArgumentException::class);
        $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'), [], 0);
    }

    public function testSummaryIsStableAndStructured(): void
    {
        [$handler] = $this->fixture();
        $summary = $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'))->summary();
        self::assertSame('fallback.unknown', $summary['classification']['rule']);
        self::assertSame('failure-fixed', $summary['failure']['id']);
        self::assertSame('fallback.rethrow', $summary['recovery']['policy']);
        self::assertTrue($summary['reporting']['succeeded']);
    }

    public function testReportingReceivesTheSameEnvelopeAndDecision(): void
    {
        [$handler, , $reporter] = $this->fixture();
        $result = $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'));
        self::assertSame($result->envelope(), $reporter->reports()[0]['envelope']);
        self::assertSame($result->recoveryDecision(), $reporter->reports()[0]['decision']);
    }

    public function testMetadataFlowsThroughFactory(): void
    {
        [$handler] = $this->fixture();
        $result = $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'), ['operation' => 'boot']);
        self::assertSame('boot', $result->envelope()->metadata()['operation']);
    }

    public function testThrowableIsClassifiedExactlyOncePerHandleCall(): void
    {
        [$handler] = $this->fixture();
        $first = $handler->handle(new RuntimeException('one'), new FailureOrigin('runtime.test'));
        $second = $handler->handle(new RuntimeException('two'), new FailureOrigin('runtime.test'));
        self::assertNotSame($first, $second);
        self::assertSame('failure-fixed', $second->envelope()->id()->value());
    }

    public function testResultExposesAllFourOrchestrationProducts(): void
    {
        [$handler] = $this->fixture();
        $result = $handler->handle(new RuntimeException('boom'), new FailureOrigin('runtime.test'));
        self::assertNotNull($result->classification());
        self::assertNotNull($result->envelope());
        self::assertNotNull($result->recoveryDecision());
        self::assertNotNull($result->reportingResult());
    }

    /** @return array{ErrorHandler,ErrorHandlingPlan,InMemoryFailureReporter} */
    private function fixture(): array
    {
        $classifier = OrderedThrowableClassifier::withUnknownFallback([]);
        $factory = new FailureEnvelopeFactory(
            new FixedFailureIdGenerator(new FailureId('failure-fixed')),
            new FrozenFailureClock(new DateTimeImmutable('2026-07-28T12:00:00+00:00')),
            new SafeFailureMetadataNormalizer(),
        );
        $decider = OrderedRecoveryDecider::withRethrowFallback([]);
        $reporter = new InMemoryFailureReporter();
        $dispatcher = new FailureReporterDispatcher([
            new FailureReportRoute('memory', new AcceptAllFailureReportFilter(), $reporter),
        ], new NullEmergencyFailureReporter());
        $plan = new ErrorHandlingPlan($classifier, $factory, $decider, $dispatcher);
        return [new ErrorHandler($plan), $plan, $reporter];
    }
}
