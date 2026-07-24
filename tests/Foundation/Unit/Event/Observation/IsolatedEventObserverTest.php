<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event\Observation;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\IsolatedEventObserver;
use Sif\Foundation\Event\Observation\ObservationFailure;
use Sif\Foundation\Tests\Fixtures\Event\RecordingObservationFailureReporter;
use stdClass;

final class IsolatedEventObserverTest extends TestCase
{
    public function testSuccessfulObservationReturnsOriginalEvent(): void
    {
        $calls = 0;
        $provider = new ListenerProvider();
        $provider->add(stdClass::class, static function (object $event) use (&$calls): void {
            self::assertInstanceOf(stdClass::class, $event);
            ++$calls;
        });
        $observer = new IsolatedEventObserver(new EventDispatcher($provider));
        $event = new stdClass();

        $result = $observer->observe($event);

        self::assertTrue($result->succeeded());
        self::assertFalse($result->failed());
        self::assertSame($event, $result->event());
        self::assertNull($result->failure());
        self::assertSame(1, $calls);
    }

    public function testListenerFailureIsIsolatedAndReported(): void
    {
        $cause = new \RuntimeException('listener exploded');
        $provider = new ListenerProvider();
        $provider->add(stdClass::class, static function (object $event) use ($cause): void {
            self::assertInstanceOf(stdClass::class, $event);
            throw $cause;
        });
        $reporter = new RecordingObservationFailureReporter();
        $observer = new IsolatedEventObserver(new EventDispatcher($provider), $reporter);
        $event = new stdClass();

        $result = $observer->observe($event);

        self::assertTrue($result->failed());
        self::assertSame($event, $result->event());
        self::assertCount(1, $reporter->failures);
        self::assertSame($cause, $reporter->failures[0]->cause());
        self::assertSame($reporter->failures[0], $result->failure());
    }

    public function testReporterFailureDoesNotEscapeIsolationBoundary(): void
    {
        $listenerFailure = new \RuntimeException('listener exploded');
        $reporterFailure = new \RuntimeException('reporter exploded');
        $provider = new ListenerProvider();
        $provider->add(stdClass::class, static function (object $event) use ($listenerFailure): void {
            self::assertInstanceOf(stdClass::class, $event);
            throw $listenerFailure;
        });
        $reporter = new RecordingObservationFailureReporter($reporterFailure);
        $observer = new IsolatedEventObserver(new EventDispatcher($provider), $reporter);

        $result = $observer->observe(new stdClass());

        self::assertTrue($result->failed());
        self::assertSame($listenerFailure, $result->failure()?->cause());
        self::assertCount(1, $reporter->failures);
    }

    public function testFailureDiagnosticIsImmutableAndSafelySerializable(): void
    {
        $event = new stdClass();
        $cause = new \RuntimeException('listener exploded');
        $occurredAt = new DateTimeImmutable('2026-07-24T12:00:00+00:00');
        $failure = new ObservationFailure($event, $cause, $occurredAt);

        self::assertSame($event, $failure->event());
        self::assertSame(stdClass::class, $failure->eventType());
        self::assertSame($cause, $failure->cause());
        self::assertSame($occurredAt, $failure->occurredAt());
        self::assertSame([
            'event_type' => stdClass::class,
            'cause_type' => \RuntimeException::class,
            'message' => 'listener exploded',
            'occurred_at' => '2026-07-24T12:00:00+00:00',
        ], $failure->jsonSerialize());
    }
}
