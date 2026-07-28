<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogRecord;
use Throwable;

interface EmergencyLogReporterInterface
{
    public function report(string $route, LogRecord $record, Throwable $failure): void;
}
