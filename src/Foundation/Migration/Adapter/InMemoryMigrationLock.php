<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Adapter;

use Sif\Foundation\Migration\Contracts\MigrationLockInterface;

final class InMemoryMigrationLock implements MigrationLockInterface
{
    private ?string $owner = null;

    public function acquire(string $owner): bool
    {
        if ($this->owner !== null) {
            return false;
        }
        $this->owner = $owner;
        return true;
    }

    public function release(string $owner): void
    {
        if ($this->owner === $owner) {
            $this->owner = null;
        }
    }

    public function owner(): ?string
    {
        return $this->owner;
    }
}
