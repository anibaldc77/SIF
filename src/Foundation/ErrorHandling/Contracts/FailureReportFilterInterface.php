<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

interface FailureReportFilterInterface
{
    public function accepts(FailureEnvelope $envelope, RecoveryDecision $decision): bool;
}
