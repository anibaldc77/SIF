<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Event;

use Sif\Foundation\Contracts\ObservationFailureReporterInterface;
use Sif\Foundation\Event\Observation\ObservationFailure;

final class RecordingObservationFailureReporter implements ObservationFailureReporterInterface
{
    /** @var list<ObservationFailure> */
    public array $failures = [];

    public function __construct(private ?\Throwable $reportingFailure = null)
    {
    }

    public function report(ObservationFailure $failure): void
    {
        $this->failures[] = $failure;

        if ($this->reportingFailure !== null) {
            throw $this->reportingFailure;
        }
    }
}
