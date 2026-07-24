<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event\Observation;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Contracts\ObservationFailureReporterInterface;
use Sif\Foundation\Event\Observation\CompositeObservationFailureReporter;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\NullObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationDiagnostic;
use Sif\Foundation\Event\Observation\ObservationDiagnosticCode;
use Sif\Foundation\Event\Observation\ObservationFailure;
use Sif\Foundation\Event\Observation\ObservationFailureReporterComposer;
use Sif\Foundation\Tests\Fixtures\Event\ThrowingObservationFailureReporter;

final class ObservationDiagnosticsAndReportingTest extends TestCase
{
    public function testDiagnosticCodeIsStable(): void
    {
        self::assertSame('OBSERVATION-001', ObservationDiagnosticCode::ListenerFailure->value);
    }

    public function testDiagnosticSerializesWithStableShape(): void
    {
        $event = new \stdClass();
        $cause = new RuntimeException('Listener failed.');
        $failure = new ObservationFailure(
            $event,
            $cause,
            new DateTimeImmutable('2026-07-24T20:00:00-03:00'),
        );

        $diagnostic = ObservationDiagnostic::fromFailure($failure);

        self::assertSame(ObservationDiagnosticCode::ListenerFailure, $diagnostic->code());
        self::assertSame($failure, $diagnostic->failure());
        self::assertSame(
            [
                'code' => 'OBSERVATION-001',
                'event_type' => \stdClass::class,
                'cause_type' => RuntimeException::class,
                'message' => 'Listener failed.',
                'occurred_at' => '2026-07-24T20:00:00-03:00',
            ],
            $diagnostic->jsonSerialize(),
        );
    }

    public function testInMemoryReporterRecordsDiagnosticsInInsertionOrder(): void
    {
        $reporter = new InMemoryObservationFailureReporter();
        $first = $this->failure('first');
        $second = $this->failure('second');

        self::assertTrue($reporter->isEmpty());

        $reporter->report($first);
        $reporter->report($second);

        self::assertFalse($reporter->isEmpty());
        self::assertSame(2, $reporter->count());
        self::assertSame($first, $reporter->diagnostics()[0]->failure());
        self::assertSame($second, $reporter->diagnostics()[1]->failure());
    }

    public function testInMemoryReporterCanBeCleared(): void
    {
        $reporter = new InMemoryObservationFailureReporter();
        $reporter->report($this->failure('failure'));

        $reporter->clear();

        self::assertTrue($reporter->isEmpty());
        self::assertSame([], $reporter->diagnostics());
    }

    public function testCompositeReporterPreservesOrderAndFailureIdentity(): void
    {
        $order = [];
        $failure = $this->failure('failure');

        $first = $this->callbackReporter(
            static function (ObservationFailure $reported) use (&$order, $failure): void {
                self::assertSame($failure, $reported);
                $order[] = 'first';
            },
        );
        $second = $this->callbackReporter(
            static function (ObservationFailure $reported) use (&$order, $failure): void {
                self::assertSame($failure, $reported);
                $order[] = 'second';
            },
        );

        $reporter = new CompositeObservationFailureReporter([$first, $second]);
        $reporter->report($failure);

        self::assertSame(['first', 'second'], $order);
    }

    public function testCompositeReporterContinuesAfterReporterException(): void
    {
        $memory = new InMemoryObservationFailureReporter();
        $failure = $this->failure('failure');
        $reporter = new CompositeObservationFailureReporter([
            new ThrowingObservationFailureReporter(),
            $memory,
        ]);

        $reporter->report($failure);

        self::assertSame(1, $memory->count());
        self::assertSame($failure, $memory->diagnostics()[0]->failure());
    }

    public function testComposerReturnsNullReporterForEmptyComposition(): void
    {
        self::assertInstanceOf(
            NullObservationFailureReporter::class,
            ObservationFailureReporterComposer::combine(),
        );
    }

    public function testComposerPreservesSingleReporterIdentity(): void
    {
        $reporter = new InMemoryObservationFailureReporter();

        self::assertSame($reporter, ObservationFailureReporterComposer::combine($reporter));
    }

    public function testComposerCreatesCompositeForMultipleReporters(): void
    {
        $reporter = ObservationFailureReporterComposer::combine(
            new InMemoryObservationFailureReporter(),
            new NullObservationFailureReporter(),
        );

        self::assertInstanceOf(CompositeObservationFailureReporter::class, $reporter);
    }

    private function failure(string $message): ObservationFailure
    {
        return new ObservationFailure(new \stdClass(), new RuntimeException($message));
    }

    /** @param callable(ObservationFailure): void $callback */
    private function callbackReporter(callable $callback): ObservationFailureReporterInterface
    {
        $closure = \Closure::fromCallable($callback);

        return new class ($closure) implements ObservationFailureReporterInterface {
            /** @param \Closure(ObservationFailure): void $callback */
            public function __construct(private readonly \Closure $callback)
            {
            }

            public function report(ObservationFailure $failure): void
            {
                ($this->callback)($failure);
            }
        };
    }
}
