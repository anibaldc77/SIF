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

final class RuntimeObservationBootReferenceIntegrationTest extends TestCase
{
    public function testBootIsObservedWithoutChangingAuthoritativeResult(): void
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

        $bootResult = $facade->boot($application);
        $observation = $facade->latestObservation();

        self::assertTrue($bootResult->succeeded());
        self::assertSame('booted', $application->runtime()->state()->value);
        self::assertSame(['boot'], $operations);
        self::assertSame($bootResult, $results[0]);
        self::assertSame(0, $reporter->count());
        self::assertNotNull($observation);
        self::assertTrue($observation->succeeded());
    }

    public function testBootListenerFailureIsDiagnosedWithoutChangingBootOutcome(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('reference boot observation listener failure');
        $listeners = new ListenerProvider();
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use ($cause): void {
                if (!$event instanceof RuntimeOperationCompleted) {
                    return;
                }

                if ($event->operation() === RuntimeOperation::Boot) {
                    throw $cause;
                }
            },
        );
        $reporter = new InMemoryObservationFailureReporter();
        $facade = new ObservationLifecycleFacade(
            $application->kernel(),
            ObservationComposer::isolated(new EventDispatcher($listeners), $reporter),
        );

        $bootResult = $facade->boot($application);
        $observation = $facade->latestObservation();
        $diagnostic = $reporter->diagnostics()[0] ?? null;

        self::assertTrue($bootResult->succeeded());
        self::assertSame('booted', $application->runtime()->state()->value);
        self::assertNotNull($observation);
        self::assertTrue($observation->failed());
        self::assertSame($cause, $observation->failure()?->cause());
        self::assertSame(1, $reporter->count());
        self::assertNotNull($diagnostic);
        self::assertSame('OBSERVATION-001', $diagnostic->code()->value);
        self::assertSame(RuntimeOperationCompleted::class, $diagnostic->failure()->event()::class);
        self::assertSame($cause, $diagnostic->failure()->cause());
    }

    public function testBootReferenceRemainsExplicitAndDoesNotReplaceApplicationKernel(): void
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
