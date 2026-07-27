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
$reporter = new InMemoryObservationFailureReporter();

$listeners->add(
    RuntimeOperationCompleted::class,
    static function (object $event): void {
        if (!$event instanceof RuntimeOperationCompleted) {
            return;
        }

        if ($event->operation() === RuntimeOperation::Shutdown) {
            throw new RuntimeException('reference shutdown observation listener failure');
        }
    },
);

$observer = ObservationComposer::isolated(
    new EventDispatcher($listeners),
    $reporter,
);
$lifecycle = new ObservationLifecycleFacade(
    $application->kernel(),
    $observer,
);

$runResult = $lifecycle->run($application);
$shutdownResult = $lifecycle->shutdown($application);
$diagnostic = $reporter->diagnostics()[0] ?? null;

printf("Run result: %s\n", $runResult->succeeded() ? 'succeeded' : 'failed');
printf("Shutdown result: %s\n", $shutdownResult->succeeded() ? 'succeeded' : 'failed');
printf("Runtime state: %s\n", $application->runtime()->state()->value);
printf("Observation result: %s\n", $lifecycle->latestObservation()?->failed() === true ? 'failed' : 'succeeded');
printf("Observation diagnostics: %d\n", $reporter->count());

if ($diagnostic !== null) {
    printf("Diagnostic code: %s\n", $diagnostic->code()->value);
    printf("Diagnostic message: %s\n", $diagnostic->failure()->cause()->getMessage());
}
