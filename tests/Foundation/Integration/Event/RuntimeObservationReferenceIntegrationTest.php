<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Event;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationComposer;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
use Sif\Foundation\Events\RuntimeOperationCompleted;
use Sif\Foundation\Framework;

final class RuntimeObservationReferenceIntegrationTest extends TestCase
{
    public function testReferenceCompositionObservesSuccessfulRuntimeWithoutReplacingResult(): void
    {
        $application = Framework::create();
        $events = [];
        $listeners = new ListenerProvider();
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use (&$events): void {
                $events[] = $event;
            },
        );
        $reporter = new InMemoryObservationFailureReporter();
        $observer = ObservationComposer::isolated(new EventDispatcher($listeners), $reporter);
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $result = $facade->run($application);

        self::assertTrue($result->succeeded());
        self::assertTrue($application->runtime()->isRunning());
        self::assertCount(1, $events);
        self::assertInstanceOf(RuntimeOperationCompleted::class, $events[0]);
        self::assertSame($result, $events[0]->result());
        self::assertTrue($reporter->isEmpty());
        self::assertTrue($facade->hasObservation());
        self::assertTrue($facade->latestObservation()?->succeeded());
    }

    public function testListenerFailureIsDiagnosedWithoutChangingRuntimeOutcome(): void
    {
        $application = Framework::create();
        $listeners = new ListenerProvider();
        $cause = new \RuntimeException('reference listener failure');
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use ($cause): void {
                throw $cause;
            },
        );
        $reporter = new InMemoryObservationFailureReporter();
        $observer = ObservationComposer::isolated(new EventDispatcher($listeners), $reporter);
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $result = $facade->run($application);
        $observation = $facade->latestObservation();

        self::assertTrue($result->succeeded());
        self::assertTrue($application->runtime()->isRunning());
        self::assertNotNull($observation);
        self::assertTrue($observation->failed());
        self::assertSame($cause, $observation->failure()?->cause());
        self::assertSame(1, $reporter->count());
        self::assertSame('OBSERVATION-001', $reporter->diagnostics()[0]->code()->value);
    }

    public function testReferenceCompositionRemainsOptInAndLeavesApplicationKernelUntouched(): void
    {
        $application = Framework::create();
        $originalKernel = $application->kernel();
        $observer = ObservationComposer::isolated(
            new EventDispatcher(new ListenerProvider()),
            new InMemoryObservationFailureReporter(),
        );
        $facade = new ObservationLifecycleFacade($originalKernel, $observer);

        self::assertSame($originalKernel, $application->kernel());
        self::assertNotSame($facade, $application->kernel());
        self::assertFalse($facade->hasObservation());
    }
}
