<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Factory;

use Sif\Foundation\Logging\Contracts\ClockInterface;
use Sif\Foundation\Logging\Contracts\LogRecordFactoryInterface;
use Sif\Foundation\Logging\Contracts\SecretRedactorInterface;
use Sif\Foundation\Logging\Contracts\StructuredValueNormalizerInterface;
use Sif\Foundation\Logging\Exceptions\LogRecordFactoryException;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\Normalization\NormalizedAttributes;
use Sif\Foundation\Logging\ThrowableMetadata;
use Throwable;

final readonly class LogRecordFactory implements LogRecordFactoryInterface
{
    public function __construct(
        private ClockInterface $clock,
        private StructuredValueNormalizerInterface $normalizer,
        private SecretRedactorInterface $redactor,
    ) {
    }

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
    ): LogRecord {
        foreach ($attributes as $key => $_value) {
            if (!is_string($key) || trim($key) === '') {
                throw LogRecordFactoryException::because('attribute keys must be non-empty strings');
            }
        }

        $normalized = NormalizedAttributes::fromRaw($attributes, $this->normalizer, $this->redactor);

        return new LogRecord(
            $this->clock->now(),
            $level,
            $channel,
            is_string($message) ? new LogMessage($message) : $message,
            $normalized->values(),
            $throwable === null ? null : ThrowableMetadata::fromThrowable($throwable),
            $recordId,
        );
    }
}
