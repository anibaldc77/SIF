<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Filtering;

use InvalidArgumentException;
use Sif\Foundation\Logging\Contracts\LogRecordFilterInterface;
use Sif\Foundation\Logging\LogRecord;

final readonly class CompositeLogRecordFilter implements LogRecordFilterInterface
{
    /** @var list<LogRecordFilterInterface> */
    private array $filters;

    /** @param list<LogRecordFilterInterface> $filters */
    public function __construct(array $filters)
    {
        if ($filters === []) {
            throw new InvalidArgumentException('At least one log record filter is required.');
        }
        $this->filters = array_values($filters);
    }

    public function accepts(LogRecord $record): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->accepts($record)) {
                return false;
            }
        }
        return true;
    }
}
