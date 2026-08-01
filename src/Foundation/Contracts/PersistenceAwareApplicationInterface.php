<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\Pdo\Runtime\PdoPersistenceRuntime;

interface PersistenceAwareApplicationInterface extends ApplicationInterface
{
    public function persistence(): ?PdoPersistenceRuntime;
}
