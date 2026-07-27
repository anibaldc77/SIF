<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

use Sif\Foundation\Contracts\ConnectionInterface;
use Sif\Foundation\Persistence\ConnectionName;

final class InMemoryConnection implements ConnectionInterface
{
    private bool $open = true;

    public function __construct(
        private readonly ConnectionName $name,
    ) {
    }

    public function name(): ConnectionName
    {
        return $this->name;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }
}
