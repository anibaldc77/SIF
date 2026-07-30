<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Migration\Runtime\MigrationRuntime;

interface MigrationAwareApplicationInterface extends ApplicationInterface
{
    public function migrations(): ?MigrationRuntime;
}
