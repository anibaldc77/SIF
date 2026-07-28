<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Processing;

use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\LogRecord;

/**
 * Named immutable composition boundary for reusable processor groups.
 */
final readonly class CompositeLogRecordProcessor implements LogRecordProcessorInterface
{
    private LogRecordProcessorPipeline $pipeline;

    /** @param iterable<LogRecordProcessorInterface> $processors */
    public function __construct(private string $name, iterable $processors = [])
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]*$/', $name)) {
            throw new \InvalidArgumentException('Processor composition name must be a portable lowercase identifier.');
        }

        $this->pipeline = new LogRecordProcessorPipeline($processors);
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return list<LogRecordProcessorInterface> */
    public function processors(): array
    {
        return $this->pipeline->processors();
    }

    public function process(LogRecord $record): LogRecord
    {
        return $this->pipeline->process($record);
    }
}
