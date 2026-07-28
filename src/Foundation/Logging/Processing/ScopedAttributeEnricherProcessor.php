<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Processing;

use Sif\Foundation\Logging\Context\ScopedLogAttributes;
use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\LogRecord;

final readonly class ScopedAttributeEnricherProcessor implements LogRecordProcessorInterface
{
    public function __construct(
        private ScopedLogAttributes $scopedAttributes,
        private bool $overwrite = false,
    ) {
    }

    public function process(LogRecord $record): LogRecord
    {
        $incoming = $this->scopedAttributes->nested();
        $attributes = $this->overwrite
            ? array_replace($record->attributes(), $incoming)
            : $record->attributes() + $incoming;

        return new LogRecord(
            $record->timestamp(),
            $record->level(),
            $record->channel(),
            $record->message(),
            $attributes,
            $record->throwable(),
            $record->recordId(),
        );
    }
}
