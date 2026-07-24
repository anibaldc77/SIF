<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use Sif\Foundation\Contracts\ObservationFailureReporterInterface;

/** Fan-out reporter that isolates every destination from the others. */
final readonly class CompositeObservationFailureReporter implements ObservationFailureReporterInterface
{
    /** @param list<ObservationFailureReporterInterface> $reporters */
    public function __construct(private array $reporters)
    {
    }

    public function report(ObservationFailure $failure): void
    {
        foreach ($this->reporters as $reporter) {
            try {
                $reporter->report($failure);
            } catch (\Throwable) {
                // Diagnostics reporting remains strictly non-authoritative.
            }
        }
    }
}
