<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Processing;

use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\LogRecord;

final readonly class AttributeEnricherProcessor implements LogRecordProcessorInterface
{
    /** @var array<string, null|bool|int|float|string|array<mixed>> */
    private array $attributes;

    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $attributes
     */
    public function __construct(array $attributes, private bool $overwrite = false)
    {
        $this->attributes = $attributes;
    }

    public function process(LogRecord $record): LogRecord
    {
        $attributes = $this->overwrite
            ? array_replace($record->attributes(), $this->attributes)
            : $record->attributes() + $this->attributes;

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
