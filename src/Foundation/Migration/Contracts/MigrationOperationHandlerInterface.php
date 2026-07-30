<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Contracts;

use Sif\Foundation\Migration\Execution\MigrationOperationResult;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;

interface MigrationOperationHandlerInterface
{
    public function supports(MigrationDescriptor $migration): bool;

    public function execute(
        MigrationDescriptor $migration,
        MigrationDirection $direction,
    ): MigrationOperationResult;
}
