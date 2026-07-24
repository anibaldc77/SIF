<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Contracts\ObservationFailureReporterInterface;

/**
 * Dispatches an event behind an isolation boundary.
 *
 * Listener and reporter exceptions never escape this observer.
 */
final readonly class IsolatedEventObserver implements EventObserverInterface
{
    private ObservationFailureReporterInterface $failureReporter;

    public function __construct(
        private EventDispatcherInterface $dispatcher,
        ?ObservationFailureReporterInterface $failureReporter = null,
    ) {
        $this->failureReporter = $failureReporter ?? new NullObservationFailureReporter();
    }

    public function observe(object $event): ObservationResult
    {
        try {
            $this->dispatcher->dispatch($event);

            return ObservationResult::success($event);
        } catch (\Throwable $cause) {
            $failure = new ObservationFailure($event, $cause);

            try {
                $this->failureReporter->report($failure);
            } catch (\Throwable) {
                // Reporting is observational and cannot escape the isolation boundary.
            }

            return ObservationResult::fromFailure($failure);
        }
    }
}
