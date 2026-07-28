<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\LogRecord;
use Throwable;

interface LogRecordFactoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(
        LogLevel $level,
        LogChannel $channel,
        string|LogMessage $message,
        array $attributes = [],
        ?Throwable $throwable = null,
        ?string $recordId = null,
    ): LogRecord;
}
