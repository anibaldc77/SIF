<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\ObservationFailureReporterInterface;

/** Explicit cardinality-aware composition for observation failure reporters. */
final class ObservationFailureReporterComposer
{
    private function __construct()
    {
    }

    public static function combine(
        ObservationFailureReporterInterface ...$reporters,
    ): ObservationFailureReporterInterface {
        return match (count($reporters)) {
            0 => new NullObservationFailureReporter(),
            1 => $reporters[0],
            default => new CompositeObservationFailureReporter(array_values($reporters)),
        };
    }
}
