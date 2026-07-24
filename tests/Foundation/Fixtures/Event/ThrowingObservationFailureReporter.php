<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Event;

use RuntimeException;
use Sif\Foundation\Contracts\ObservationFailureReporterInterface;
use Sif\Foundation\Event\Observation\ObservationFailure;

final readonly class ThrowingObservationFailureReporter implements ObservationFailureReporterInterface
{
    public function report(ObservationFailure $failure): void
    {
        throw new RuntimeException('Reporter failure.');
    }
}
