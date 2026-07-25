<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event\Observation;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Event\Observation\ObservationFailure;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
use Sif\Foundation\Event\Observation\ObservationResult;
use Sif\Foundation\Events\RuntimeOperationCompleted;
use Sif\Foundation\Framework;
use Sif\Foundation\Tests\Fixtures\Event\RecordingEventObserver;

final class ObservationLifecycleFacadeTest extends TestCase
{
    public function testRunReturnsExactAuthoritativeResultAndRecordsObservation(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver();
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        self::assertFalse($facade->hasObservation());
        self::assertNull($facade->latestObservation());

        $result = $facade->run($application);
        $observation = $facade->latestObservation();

        self::assertTrue($result->succeeded());
        self::assertTrue($facade->hasObservation());
        self::assertNotNull($observation);
        self::assertTrue($observation->succeeded());
        self::assertCount(1, $observer->events);
        self::assertSame($result, self::completed($observer->events[0])->result());
    }

    public function testBootRunAndShutdownReplaceLatestObservationDeterministically(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver();
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $boot = $facade->boot($application);
        self::assertSame($boot, self::latestEvent($facade)->result());

        $shutdown = $facade->shutdown($application);
        self::assertSame($shutdown, self::latestEvent($facade)->result());

        self::assertCount(2, $observer->events);
    }

    public function testClearRemovesOnlyFacadeObservationState(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver();
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $result = $facade->run($application);
        $facade->clearObservation();

        self::assertTrue($result->succeeded());
        self::assertFalse($facade->hasObservation());
        self::assertNull($facade->latestObservation());
        self::assertTrue($application->runtime()->isRunning());
        self::assertCount(1, $observer->events);
    }

    public function testObserverFailureIsExposedWithoutChangingRuntimeResult(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('observer failure');
        $observer = new class ($cause) implements EventObserverInterface {
            public function __construct(private \Throwable $cause)
            {
            }

            public function observe(object $event): ObservationResult
            {
                return ObservationResult::fromFailure(
                    new ObservationFailure($event, $this->cause),
                );
            }
        };
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $result = $facade->run($application);
        $observation = $facade->latestObservation();

        self::assertTrue($result->succeeded());
        self::assertTrue($application->runtime()->isRunning());
        self::assertNotNull($observation);
        self::assertTrue($observation->failed());
        self::assertSame($cause, $observation->failure()?->cause());
    }

    public function testThrownObserverExceptionIsConvertedIntoLatestFailure(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('observer escaped');
        $observer = new class ($cause) implements EventObserverInterface {
            public function __construct(private \Throwable $cause)
            {
            }

            public function observe(object $event): ObservationResult
            {
                throw $this->cause;
            }
        };
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $result = $facade->run($application);
        $observation = $facade->latestObservation();

        self::assertTrue($result->succeeded());
        self::assertNotNull($observation);
        self::assertTrue($observation->failed());
        self::assertSame($cause, $observation->failure()?->cause());
    }

    public function testFacadeRemainsExplicitAndDoesNotReplaceApplicationKernel(): void
    {
        $application = Framework::create();
        $original = $application->kernel();
        $facade = new ObservationLifecycleFacade($original, new RecordingEventObserver());

        self::assertSame($original, $application->kernel());
        self::assertNotSame($facade, $application->kernel());
    }

    private static function latestEvent(ObservationLifecycleFacade $facade): RuntimeOperationCompleted
    {
        $observation = $facade->latestObservation();
        self::assertNotNull($observation);
        self::assertTrue($observation->succeeded());

        return self::completed($observation->event());
    }

    private static function completed(object $event): RuntimeOperationCompleted
    {
        self::assertInstanceOf(RuntimeOperationCompleted::class, $event);

        return $event;
    }
}
