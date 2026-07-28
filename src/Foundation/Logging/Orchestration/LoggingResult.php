<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Orchestration;

use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\Routing\LogDispatchReport;

final readonly class LoggingResult
{
    public function __construct(
        private LogRecord $record,
        private LogDispatchReport $dispatchReport,
    ) {
    }

    public function record(): LogRecord
    {
        return $this->record;
    }

    public function dispatchReport(): LogDispatchReport
    {
        return $this->dispatchReport;
    }

    public function succeeded(): bool
    {
        return $this->dispatchReport->succeeded();
    }
}
