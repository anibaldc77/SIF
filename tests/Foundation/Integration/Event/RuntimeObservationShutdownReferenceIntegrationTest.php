<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Event;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationComposer;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
use Sif\Foundation\Event\Observation\RuntimeOperation;
use Sif\Foundation\Events\RuntimeOperationCompleted;
use Sif\Foundation\Framework;

final class RuntimeObservationShutdownReferenceIntegrationTest extends TestCase
{
    public function testRunThenShutdownIsObservedWithoutChangingAuthoritativeResults(): void
    {
        $application = Framework::create();
        $operations = [];
        $results = [];
        $listeners = new ListenerProvider();
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use (&$operations, &$results): void {
                if (!$event instanceof RuntimeOperationCompleted) {
                    return;
                }

                $operations[] = $event->operation()->value;
                $results[] = $event->result();
            },
        );
        $reporter = new InMemoryObservationFailureReporter();
        $facade = new ObservationLifecycleFacade(
            $application->kernel(),
            ObservationComposer::isolated(new EventDispatcher($listeners), $reporter),
        );

        $runResult = $facade->run($application);
        $shutdownResult = $facade->shutdown($application);

        self::assertTrue($runResult->succeeded());
        self::assertTrue($shutdownResult->succeeded());
        self::assertSame('stopped', $application->runtime()->state()->value);
        self::assertSame(['run', 'shutdown'], $operations);
        self::assertSame($runResult, $results[0]);
        self::assertSame($shutdownResult, $results[1]);
        self::assertSame(0, $reporter->count());
        self::assertNotNull($facade->latestObservation());
        self::assertTrue($facade->latestObservation()->succeeded());
    }

    public function testShutdownListenerFailureIsDiagnosedWithoutChangingShutdownOutcome(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('reference shutdown observation listener failure');
        $listeners = new ListenerProvider();
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use ($cause): void {
                if (!$event instanceof RuntimeOperationCompleted) {
                    return;
                }

                if ($event->operation() === RuntimeOperation::Shutdown) {
                    throw $cause;
                }
            },
        );
        $reporter = new InMemoryObservationFailureReporter();
        $facade = new ObservationLifecycleFacade(
            $application->kernel(),
            ObservationComposer::isolated(new EventDispatcher($listeners), $reporter),
        );

        $runResult = $facade->run($application);
        $shutdownResult = $facade->shutdown($application);
        $observation = $facade->latestObservation();
        $diagnostic = $reporter->diagnostics()[0] ?? null;

        self::assertTrue($runResult->succeeded());
        self::assertTrue($shutdownResult->succeeded());
        self::assertSame('stopped', $application->runtime()->state()->value);
        self::assertNotNull($observation);
        self::assertTrue($observation->failed());
        self::assertSame($cause, $observation->failure()?->cause());
        self::assertSame(1, $reporter->count());
        self::assertNotNull($diagnostic);
        self::assertSame('OBSERVATION-001', $diagnostic->code()->value);
        self::assertSame(RuntimeOperationCompleted::class, $diagnostic->failure()->event()::class);
        self::assertSame('reference shutdown observation listener failure', $diagnostic->failure()->cause()->getMessage());
    }

    public function testShutdownReferenceRemainsExplicitAndDoesNotReplaceApplicationKernel(): void
    {
        $application = Framework::create();
        $originalKernel = $application->kernel();
        $facade = new ObservationLifecycleFacade(
            $originalKernel,
            ObservationComposer::isolated(
                new EventDispatcher(new ListenerProvider()),
                new InMemoryObservationFailureReporter(),
            ),
        );

        self::assertSame($originalKernel, $application->kernel());
        self::assertNotSame($facade, $application->kernel());
        self::assertFalse($facade->hasObservation());
    }
}
