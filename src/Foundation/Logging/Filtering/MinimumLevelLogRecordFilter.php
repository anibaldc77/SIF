<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Filtering;

use Sif\Foundation\Logging\Contracts\LogRecordFilterInterface;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogRecord;

final readonly class MinimumLevelLogRecordFilter implements LogRecordFilterInterface
{
    public function __construct(private LogLevel $minimum)
    {
    }

    public function accepts(LogRecord $record): bool
    {
        return $record->level()->isAtLeast($this->minimum);
    }
}
