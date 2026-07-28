<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Filtering;

use Sif\Foundation\Logging\Contracts\LogRecordFilterInterface;
use Sif\Foundation\Logging\LogRecord;

final readonly class AcceptAllLogRecordFilter implements LogRecordFilterInterface
{
    public function accepts(LogRecord $record): bool
    {
        return true;
    }
}
