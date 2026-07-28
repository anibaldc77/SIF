<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Handling;

use Sif\Foundation\Logging\Contracts\LogHandlerInterface;
use Sif\Foundation\Logging\LogRecord;

final class InMemoryLogHandler implements LogHandlerInterface
{
    /** @var list<LogRecord> */
    private array $records = [];

    public function handle(LogRecord $record): void
    {
        $this->records[] = $record;
    }

    /** @return list<LogRecord> */
    public function records(): array
    {
        return $this->records;
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function clear(): void
    {
        $this->records = [];
    }
}
