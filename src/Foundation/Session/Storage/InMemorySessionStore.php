<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Storage;

use Sif\Foundation\Session\Contracts\SessionStoreInterface;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionRecord;

final class InMemorySessionStore implements SessionStoreInterface
{
    /** @var array<string, SessionRecord> */
    private array $records = [];

    public function read(SessionId $id): ?SessionRecord
    {
        return $this->records[$id->value()] ?? null;
    }

    public function write(SessionRecord $record): void
    {
        $this->records[$record->id()->value()] = $record;
    }

    public function delete(SessionId $id): void
    {
        unset($this->records[$id->value()]);
    }

    public function count(): int
    {
        return count($this->records);
    }
}
