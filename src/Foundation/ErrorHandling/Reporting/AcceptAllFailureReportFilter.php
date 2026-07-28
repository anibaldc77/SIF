<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use Sif\Foundation\ErrorHandling\Contracts\FailureReportFilterInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

final readonly class AcceptAllFailureReportFilter implements FailureReportFilterInterface
{
    public function accepts(FailureEnvelope $envelope, RecoveryDecision $decision): bool
    {
        return true;
    }
}
