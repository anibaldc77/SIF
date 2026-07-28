<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Handling;

use Sif\Foundation\Logging\Contracts\EmergencyLogReporterInterface;
use Sif\Foundation\Logging\LogRecord;
use Throwable;

final readonly class NullEmergencyLogReporter implements EmergencyLogReporterInterface
{
    public function report(string $route, LogRecord $record, Throwable $failure): void
    {
    }
}
