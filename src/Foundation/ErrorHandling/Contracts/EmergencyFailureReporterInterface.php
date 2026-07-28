<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;
use Throwable;

interface EmergencyFailureReporterInterface
{
    public function report(
        string $reporter,
        FailureEnvelope $envelope,
        RecoveryDecision $decision,
        Throwable $failure,
    ): void;
}
