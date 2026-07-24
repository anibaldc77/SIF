<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Contracts\ObservationFailureReporterInterface;

/** Explicit composition helpers for opt-in runtime observation. */
final class ObservationComposer
{
    private function __construct()
    {
    }

    public static function isolated(
        EventDispatcherInterface $dispatcher,
        ?ObservationFailureReporterInterface $failureReporter = null,
    ): IsolatedEventObserver {
        return new IsolatedEventObserver($dispatcher, $failureReporter);
    }

    public static function combine(EventObserverInterface ...$observers): EventObserverInterface
    {
        return match (count($observers)) {
            0 => new NullEventObserver(),
            1 => $observers[0],
            default => new CompositeEventObserver(array_values($observers)),
        };
    }

    public static function kernel(
        KernelInterface $kernel,
        EventObserverInterface $observer,
    ): ObservedKernel {
        return new ObservedKernel($kernel, $observer);
    }
}
