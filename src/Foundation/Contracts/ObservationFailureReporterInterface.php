<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Event\Observation\ObservationFailure;

/** Receives an immutable diagnostic when event observation fails. */
interface ObservationFailureReporterInterface
{
    public function report(ObservationFailure $failure): void;
}
