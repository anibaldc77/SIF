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

$bootApplication = Framework::create();
$bootOperations = [];
$bootListeners = new ListenerProvider();
$bootListeners->add(
    RuntimeOperationCompleted::class,
    static function (object $event) use (&$bootOperations): void {
        if ($event instanceof RuntimeOperationCompleted) {
            $bootOperations[] = $event->operation()->value;
        }
    },
);
$bootFacade = new ObservationLifecycleFacade(
    $bootApplication->kernel(),
    ObservationComposer::isolated(
        new EventDispatcher($bootListeners),
        new InMemoryObservationFailureReporter(),
    ),
);
$bootResult = $bootFacade->boot($bootApplication);

$runApplication = Framework::create();
$runOperations = [];
$runReporter = new InMemoryObservationFailureReporter();
$runListeners = new ListenerProvider();
$runListeners->add(
    RuntimeOperationCompleted::class,
    static function (object $event) use (&$runOperations): void {
        if ($event instanceof RuntimeOperationCompleted) {
            $runOperations[] = $event->operation()->value;
        }
    },
);
$runFacade = new ObservationLifecycleFacade(
    $runApplication->kernel(),
    ObservationComposer::isolated(new EventDispatcher($runListeners), $runReporter),
);
$runResult = $runFacade->run($runApplication);
$shutdownResult = $runFacade->shutdown($runApplication);

printf("Boot result: %s\n", $bootResult->succeeded() ? 'succeeded' : 'failed');
printf("Boot state: %s\n", $bootApplication->runtime()->state()->value);
printf("Boot operations: %s\n", implode(', ', $bootOperations));
printf("Run result: %s\n", $runResult->succeeded() ? 'succeeded' : 'failed');
printf("Shutdown result: %s\n", $shutdownResult->succeeded() ? 'succeeded' : 'failed');
printf("Run lifecycle state: %s\n", $runApplication->runtime()->state()->value);
printf("Run lifecycle operations: %s\n", implode(', ', $runOperations));
printf("Observation diagnostics: %d\n", $runReporter->count());
