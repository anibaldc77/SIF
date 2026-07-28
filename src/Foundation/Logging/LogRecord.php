<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging;

use Sif\Foundation\Logging\Exceptions\InvalidLogRecordException;

final readonly class LogRecord
{
    /** @var array<string, null|bool|int|float|string|array<mixed>> */
    private array $attributes;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private LogTimestamp $timestamp,
        private LogLevel $level,
        private LogChannel $channel,
        private LogMessage $message,
        array $attributes = [],
        private ?ThrowableMetadata $throwable = null,
        private ?string $recordId = null,
    ) {
        foreach ($attributes as $key => $value) {
            if (!is_string($key) || $key === '') {
                throw InvalidLogRecordException::because('attribute keys must be non-empty strings');
            }
            if (!$this->isSupportedValue($value)) {
                throw InvalidLogRecordException::because(sprintf('attribute "%s" contains an unsupported value', $key));
            }
        }
        if ($recordId !== null && trim($recordId) === '') {
            throw InvalidLogRecordException::because('record identifier must not be empty');
        }
        /** @var array<string, null|bool|int|float|string|array<mixed>> $attributes */
        $this->attributes = $attributes;
    }

    public function timestamp(): LogTimestamp { return $this->timestamp; }
    public function level(): LogLevel { return $this->level; }
    public function channel(): LogChannel { return $this->channel; }
    public function message(): LogMessage { return $this->message; }
    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    public function attributes(): array { return $this->attributes; }
    public function throwable(): ?ThrowableMetadata { return $this->throwable; }
    public function recordId(): ?string { return $this->recordId; }

    private function isSupportedValue(mixed $value): bool
    {
        if ($value === null || is_scalar($value)) {
            return true;
        }
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $nested) {
            if (!$this->isSupportedValue($nested)) {
                return false;
            }
        }
        return true;
    }
}
