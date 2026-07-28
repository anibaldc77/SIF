<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Processing;

use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\Exceptions\LogProcessorException;
use Sif\Foundation\Logging\LogRecord;
use Throwable;

final readonly class LogRecordProcessorPipeline implements LogRecordProcessorInterface
{
    /** @var list<LogRecordProcessorInterface> */
    private array $processors;

    /** @param iterable<LogRecordProcessorInterface> $processors */
    public function __construct(iterable $processors = [])
    {
        $collected = [];
        foreach ($processors as $processor) {
            $collected[] = $processor;
        }
        $this->processors = $collected;
    }

    public function process(LogRecord $record): LogRecord
    {
        $current = $record;
        foreach ($this->processors as $position => $processor) {
            try {
                $current = $processor->process($current);
            } catch (Throwable $cause) {
                throw LogProcessorException::failed($position, $processor::class, $cause);
            }
        }
        return $current;
    }

    /** @return list<LogRecordProcessorInterface> */
    public function processors(): array
    {
        return $this->processors;
    }
}
