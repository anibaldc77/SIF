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

final class RuntimeObservationFailureReferenceIntegrationTest extends TestCase
{
    public function testListenerFailureProducesStableDiagnosticWithoutChangingRuntimeOutcome(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('reference observation listener failure');
        $listeners = new ListenerProvider();
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use ($cause): void {
                if (!$event instanceof RuntimeOperationCompleted) {
                    return;
                }

                throw $cause;
            },
        );
        $reporter = new InMemoryObservationFailureReporter();
        $observer = ObservationComposer::isolated(new EventDispatcher($listeners), $reporter);
        $facade = new ObservationLifecycleFacade($application->kernel(), $observer);

        $result = $facade->run($application);
        $observation = $facade->latestObservation();
        $diagnostic = $reporter->diagnostics()[0] ?? null;

        self::assertTrue($result->succeeded());
        self::assertTrue($application->runtime()->isRunning());
        self::assertNotNull($observation);
        self::assertTrue($observation->failed());
        self::assertSame($cause, $observation->failure()?->cause());
        self::assertNotNull($diagnostic);
        self::assertSame('OBSERVATION-001', $diagnostic->code()->value);
        self::assertSame(
            [
                'code' => 'OBSERVATION-001',
                'event_type' => RuntimeOperationCompleted::class,
                'cause_type' => \RuntimeException::class,
                'message' => 'reference observation listener failure',
                'occurred_at' => $diagnostic->failure()->occurredAt()->format(DATE_ATOM),
            ],
            $diagnostic->jsonSerialize(),
        );
    }

    public function testFailureReferenceRemainsOptInAndDoesNotReplaceApplicationKernel(): void
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
