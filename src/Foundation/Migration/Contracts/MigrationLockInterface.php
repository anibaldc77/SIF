<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Contracts;

interface MigrationLockInterface
{
    public function acquire(string $owner): bool;

    public function release(string $owner): void;
}
