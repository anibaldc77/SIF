<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Migration\Runtime\MigrationRuntime;

interface MutableMigrationApplicationInterface extends MigrationAwareApplicationInterface
{
    public function setMigrations(MigrationRuntime $migrations): void;
}
