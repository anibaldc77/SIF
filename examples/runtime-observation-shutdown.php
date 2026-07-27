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
$operations = [];

$listeners->add(
    RuntimeOperationCompleted::class,
    static function (object $event) use (&$operations): void {
        if (!$event instanceof RuntimeOperationCompleted) {
            return;
        }

        $operations[] = $event->operation()->value;
    },
);

$reporter = new InMemoryObservationFailureReporter();
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

printf("Run result: %s\n", $runResult->succeeded() ? 'succeeded' : 'failed');
printf("Shutdown result: %s\n", $shutdownResult->succeeded() ? 'succeeded' : 'failed');
printf("Runtime state: %s\n", $application->runtime()->state()->value);
printf("Observed operations: %s\n", implode(', ', $operations));
printf("Observation diagnostics: %d\n", $reporter->count());
