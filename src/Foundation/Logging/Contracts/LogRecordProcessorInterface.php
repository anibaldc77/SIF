<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogRecord;

interface LogRecordProcessorInterface
{
    public function process(LogRecord $record): LogRecord;
}
