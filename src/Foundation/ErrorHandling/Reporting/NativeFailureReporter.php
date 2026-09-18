<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use Sif\Foundation\ErrorHandling\Contracts\FailureReporterInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

/** Minimal default reporting without exposing exception messages or request data. */
final class NativeFailureReporter implements FailureReporterInterface
{
    public function report(FailureEnvelope $envelope, RecoveryDecision $decision): void
    {
        error_log('SIF request failure: ' . $envelope->id()->value());
    }
}
