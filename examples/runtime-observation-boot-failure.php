<?php

declare(strict_types=1);

use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationComposer;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
use Sif\Foundation\Event\Observation\RuntimeOperation;
use Sif\Foundation\Events\RuntimeOperationCompleted;
use Sif\Foundation\Framework;

require dirname(__DIR__) . '/vendor/autoload.php';

$application = Framework::create();
$listeners = new ListenerProvider();
$listeners->add(
    RuntimeOperationCompleted::class,
    static function (object $event): void {
        if (!$event instanceof RuntimeOperationCompleted) {
            return;
        }

        if ($event->operation() === RuntimeOperation::Boot) {
            throw new RuntimeException('reference boot observation listener failure');
        }
    },
);

$reporter = new InMemoryObservationFailureReporter();
$lifecycle = new ObservationLifecycleFacade(
    $application->kernel(),
    ObservationComposer::isolated(new EventDispatcher($listeners), $reporter),
);

$bootResult = $lifecycle->boot($application);
$observation = $lifecycle->latestObservation();
$diagnostic = $reporter->diagnostics()[0] ?? null;

printf("Boot result: %s\n", $bootResult->succeeded() ? 'succeeded' : 'failed');
printf("Runtime state: %s\n", $application->runtime()->state()->value);
printf("Observation result: %s\n", $observation !== null && $observation->failed() ? 'failed' : 'succeeded');
printf("Observation diagnostics: %d\n", $reporter->count());
printf("Diagnostic code: %s\n", $diagnostic?->code()->value ?? 'none');
printf("Diagnostic message: %s\n", $diagnostic?->failure()->cause()->getMessage() ?? 'none');
