<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Orchestration;

use Sif\Foundation\Logging\Contracts\LoggerInterface;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\Planning\LoggingPlan;
use Throwable;

final readonly class StructuredLogger implements LoggerInterface
{
    public function __construct(private LoggingPlan $plan)
    {
    }

    /** @param array<string, mixed> $attributes */
    public function log(
        LogLevel $level,
        string|LogMessage $message,
        array $attributes = [],
        ?Throwable $throwable = null,
        ?LogChannel $channel = null,
        ?string $recordId = null,
    ): LoggingResult {
        $record = $this->plan->recordFactory()->create(
            $level,
            $channel ?? $this->plan->defaultChannel(),
            $message,
            $attributes,
            $throwable,
            $recordId,
        );
        $processed = $this->plan->processor()->process($record);

        return new LoggingResult($processed, $this->plan->router()->dispatch($processed));
    }

    /** @param array<string, mixed> $attributes */
    public function debug(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::debug(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function info(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::info(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function notice(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::notice(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function warning(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::warning(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function error(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::error(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function critical(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::critical(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function alert(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::alert(), $message, $attributes, $throwable);
    }

    /** @param array<string, mixed> $attributes */
    public function emergency(string|LogMessage $message, array $attributes = [], ?Throwable $throwable = null): LoggingResult
    {
        return $this->log(LogLevel::emergency(), $message, $attributes, $throwable);
    }
}
