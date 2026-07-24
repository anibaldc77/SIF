<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\ObservationFailureReporterInterface;

/** Default reporter for hosts that intentionally ignore isolated diagnostics. */
final readonly class NullObservationFailureReporter implements ObservationFailureReporterInterface
{
    public function report(ObservationFailure $failure): void
    {
    }
}
