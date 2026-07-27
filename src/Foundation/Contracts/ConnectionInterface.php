<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\ConnectionName;

interface ConnectionInterface
{
    public function name(): ConnectionName;

    public function isOpen(): bool;

    public function close(): void;
}
