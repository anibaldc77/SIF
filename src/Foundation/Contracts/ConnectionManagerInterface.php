<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\ConnectionName;

interface ConnectionManagerInterface
{
    public function connection(
        ?ConnectionName $name = null,
    ): ConnectionInterface;

    public function has(ConnectionName $name): bool;
}
