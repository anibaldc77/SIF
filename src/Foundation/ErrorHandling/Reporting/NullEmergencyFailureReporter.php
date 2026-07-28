<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use Sif\Foundation\ErrorHandling\Contracts\EmergencyFailureReporterInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;
use Throwable;

final readonly class NullEmergencyFailureReporter implements EmergencyFailureReporterInterface
{
    public function report(
        string $reporter,
        FailureEnvelope $envelope,
        RecoveryDecision $decision,
        Throwable $failure,
    ): void {
    }
}
