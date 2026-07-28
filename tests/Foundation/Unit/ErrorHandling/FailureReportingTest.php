<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\ErrorHandling\Contracts\EmergencyFailureReporterInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureReporterInterface;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\FailureId;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\FailureSeverity;
use Sif\Foundation\ErrorHandling\FailureTimestamp;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryAction;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;
use Sif\Foundation\ErrorHandling\Reporting\AcceptAllFailureReportFilter;
use Sif\Foundation\ErrorHandling\Reporting\CompositeFailureReportFilter;
use Sif\Foundation\ErrorHandling\Reporting\FailureCategoryFilter;
use Sif\Foundation\ErrorHandling\Reporting\FailureReportRoute;
use Sif\Foundation\ErrorHandling\Reporting\FailureReporterDispatcher;
use Sif\Foundation\ErrorHandling\Reporting\InMemoryFailureReporter;
use Sif\Foundation\ErrorHandling\Reporting\MinimumFailureSeverityFilter;
use Sif\Foundation\ErrorHandling\Reporting\NullEmergencyFailureReporter;
use Throwable;

final class FailureReportingTest extends TestCase
{
    public function testInMemoryReporterPreservesEnvelopeAndDecisionIdentity(): void
    {
        $reporter = new InMemoryFailureReporter();
        $envelope = $this->envelope();
        $decision = $this->decision();

        $reporter->report($envelope, $decision);

        self::assertSame($envelope, $reporter->reports()[0]['envelope']);
        self::assertSame($decision, $reporter->reports()[0]['decision']);
        self::assertSame(1, $reporter->count());
    }

    public function testMinimumSeverityFilterUsesCanonicalOrdering(): void
    {
        $filter = new MinimumFailureSeverityFilter(FailureSeverity::warning());
        self::assertTrue($filter->accepts($this->envelope(FailureSeverity::error()), $this->decision()));
        self::assertFalse($filter->accepts($this->envelope(FailureSeverity::info()), $this->decision()));
    }

    public function testCategoryFilterAcceptsConfiguredCategories(): void
    {
        $filter = new FailureCategoryFilter([FailureCategory::infrastructure(), FailureCategory::dependency()]);
        self::assertTrue($filter->accepts($this->envelope(category: FailureCategory::infrastructure()), $this->decision()));
        self::assertFalse($filter->accepts($this->envelope(category: FailureCategory::validation()), $this->decision()));
    }

    public function testCompositeFilterUsesAndSemantics(): void
    {
        $filter = new CompositeFailureReportFilter([
            new MinimumFailureSeverityFilter(FailureSeverity::error()),
            new FailureCategoryFilter([FailureCategory::infrastructure()]),
        ]);

        self::assertTrue($filter->accepts($this->envelope(), $this->decision()));
        self::assertFalse($filter->accepts($this->envelope(category: FailureCategory::validation()), $this->decision()));
    }

    public function testDispatcherPreservesRouteOrderAndReportsAllMatchingRoutes(): void
    {
        $first = new InMemoryFailureReporter();
        $second = new InMemoryFailureReporter();
        $dispatcher = new FailureReporterDispatcher([
            new FailureReportRoute('memory.first', new AcceptAllFailureReportFilter(), $first),
            new FailureReportRoute('memory.second', new AcceptAllFailureReportFilter(), $second),
        ], new NullEmergencyFailureReporter());

        $result = $dispatcher->dispatch($this->envelope(), $this->decision());

        self::assertSame(['memory.first', 'memory.second'], $result->reportedRoutes());
        self::assertSame(1, $first->count());
        self::assertSame(1, $second->count());
    }

    public function testFilteredRoutesAreRecordedWithoutInvokingReporter(): void
    {
        $reporter = new InMemoryFailureReporter();
        $dispatcher = new FailureReporterDispatcher([
            new FailureReportRoute(
                'critical.only',
                new MinimumFailureSeverityFilter(FailureSeverity::critical()),
                $reporter,
            ),
        ], new NullEmergencyFailureReporter());

        $result = $dispatcher->dispatch($this->envelope(FailureSeverity::warning()), $this->decision());

        self::assertSame(['critical.only'], $result->filteredRoutes());
        self::assertSame(0, $reporter->count());
    }

    public function testReporterFailureIsIsolatedAndLaterRoutesContinue(): void
    {
        $failure = new RuntimeException('reporter failed');
        $failingReporter = new class ($failure) implements FailureReporterInterface {
            public function __construct(private Throwable $failure) {}
            public function report(FailureEnvelope $envelope, RecoveryDecision $decision): void
            {
                throw $this->failure;
            }
        };
        $healthyReporter = new InMemoryFailureReporter();
        $dispatcher = new FailureReporterDispatcher([
            new FailureReportRoute('failure.primary', new AcceptAllFailureReportFilter(), $failingReporter),
            new FailureReportRoute('memory.fallback', new AcceptAllFailureReportFilter(), $healthyReporter),
        ], new NullEmergencyFailureReporter());

        $result = $dispatcher->dispatch($this->envelope(), $this->decision());

        self::assertSame(['memory.fallback'], $result->reportedRoutes());
        self::assertSame(1, $result->failureCount());
        self::assertSame($failure, $result->failures()[0]->failure());
        self::assertSame(1, $healthyReporter->count());
    }

    public function testEmergencyReporterReceivesOriginalReporterFailure(): void
    {
        $reporterFailure = new RuntimeException('ordinary reporter failure');
        $emergency = new class implements EmergencyFailureReporterInterface {
            /** @var list<array{reporter:string,failure:Throwable}> */
            public array $calls = [];
            public function report(string $reporter, FailureEnvelope $envelope, RecoveryDecision $decision, Throwable $failure): void
            {
                $this->calls[] = ['reporter' => $reporter, 'failure' => $failure];
            }
        };
        $failingReporter = new class ($reporterFailure) implements FailureReporterInterface {
            public function __construct(private Throwable $failure) {}
            public function report(FailureEnvelope $envelope, RecoveryDecision $decision): void { throw $this->failure; }
        };
        $dispatcher = new FailureReporterDispatcher([
            new FailureReportRoute('remote.primary', new AcceptAllFailureReportFilter(), $failingReporter),
        ], $emergency);

        $dispatcher->dispatch($this->envelope(), $this->decision());

        self::assertSame('remote.primary', $emergency->calls[0]['reporter']);
        self::assertSame($reporterFailure, $emergency->calls[0]['failure']);
    }

    public function testEmergencyReporterFailureIsTerminalAndDoesNotEscape(): void
    {
        $failingReporter = new class implements FailureReporterInterface {
            public function report(FailureEnvelope $envelope, RecoveryDecision $decision): void { throw new RuntimeException('primary'); }
        };
        $failingEmergency = new class implements EmergencyFailureReporterInterface {
            public function report(string $reporter, FailureEnvelope $envelope, RecoveryDecision $decision, Throwable $failure): void
            {
                throw new RuntimeException('emergency');
            }
        };
        $dispatcher = new FailureReporterDispatcher([
            new FailureReportRoute('primary', new AcceptAllFailureReportFilter(), $failingReporter),
        ], $failingEmergency);

        $result = $dispatcher->dispatch($this->envelope(), $this->decision());
        self::assertSame(1, $result->failureCount());
        self::assertFalse($result->succeeded());
    }

    public function testDuplicateRouteNamesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $reporter = new InMemoryFailureReporter();
        new FailureReporterDispatcher([
            new FailureReportRoute('duplicate', new AcceptAllFailureReportFilter(), $reporter),
            new FailureReportRoute('duplicate', new AcceptAllFailureReportFilter(), $reporter),
        ], new NullEmergencyFailureReporter());
    }

    private function envelope(
        ?FailureSeverity $severity = null,
        ?FailureCategory $category = null,
    ): FailureEnvelope {
        return new FailureEnvelope(
            new FailureId('failure-test'),
            new FailureTimestamp(new DateTimeImmutable('2026-07-28T20:00:00+00:00')),
            $category ?? FailureCategory::infrastructure(),
            $severity ?? FailureSeverity::error(),
            FailureDisposition::transient(),
            new FailureOrigin('runtime.test'),
            new RuntimeException('application failure'),
        );
    }

    private function decision(): RecoveryDecision
    {
        return new RecoveryDecision(RecoveryAction::rethrow(), 'test.rethrow');
    }
}
