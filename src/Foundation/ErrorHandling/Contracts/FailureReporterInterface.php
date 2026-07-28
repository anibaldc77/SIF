<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

interface FailureReporterInterface
{
    public function report(FailureEnvelope $envelope, RecoveryDecision $decision): void;
}
