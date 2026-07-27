<?php

declare(strict_types=1);

use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationComposer;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
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

        throw new RuntimeException('reference observation listener failure');
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

$result = $lifecycle->run($application);
$diagnostic = $reporter->diagnostics()[0] ?? null;

printf("Runtime result: %s\n", $result->succeeded() ? 'succeeded' : 'failed');
printf("Runtime state: %s\n", $application->runtime()->isRunning() ? 'running' : 'not-running');
printf("Observation result: %s\n", $lifecycle->latestObservation()?->failed() === true ? 'failed' : 'succeeded');
printf("Observation diagnostics: %d\n", $reporter->count());

if ($diagnostic !== null) {
    printf("Diagnostic code: %s\n", $diagnostic->code()->value);
    printf("Diagnostic message: %s\n", $diagnostic->failure()->cause()->getMessage());
}
