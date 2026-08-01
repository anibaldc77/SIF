<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\Pdo\Runtime\PdoPersistenceRuntime;

interface MutablePersistenceApplicationInterface extends PersistenceAwareApplicationInterface
{
    public function setPersistence(PdoPersistenceRuntime $persistence): void;
}
