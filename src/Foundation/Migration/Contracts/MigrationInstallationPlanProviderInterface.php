<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Contracts;

use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\Selection\MigrationExecutionPlan;

interface MigrationInstallationPlanProviderInterface
{
    /** @return array{MigrationExecutionPlan, MigrationExecutionAuthorization} */
    public function applyPlan(MutationDescriptor $mutation): array;

    /** @return array{MigrationExecutionPlan, MigrationExecutionAuthorization} */
    public function compensationPlan(MutationDescriptor $mutation): array;
}
