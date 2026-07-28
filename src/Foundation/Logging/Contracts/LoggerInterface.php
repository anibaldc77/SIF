<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\Orchestration\LoggingResult;
use Throwable;

interface LoggerInterface
{
    /** @param array<string, mixed> $attributes */
    public function log(
        LogLevel $level,
        string|LogMessage $message,
        array $attributes = [],
        ?Throwable $throwable = null,
        ?LogChannel $channel = null,
        ?string $recordId = null,
    ): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function debug(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function info(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function notice(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function warning(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function error(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function critical(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function alert(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;

    /** @param array<string, mixed> $attributes */
    public function emergency(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult;
}
