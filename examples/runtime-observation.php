<?php

declare(strict_types=1);

use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
use Sif\Foundation\Event\Observation\ObservationComposer;
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

        printf(
            "Observed runtime operation: %s (%s)\n",
            $event->operation()->value,
            $event->result()->succeeded() ? 'succeeded' : 'failed',
        );
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

printf("Runtime result: %s\n", $result->succeeded() ? 'succeeded' : 'failed');
printf("Observation diagnostics: %d\n", $reporter->count());
