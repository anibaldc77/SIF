<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Resources\Contracts\ResourcePathResolverInterface;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;

interface ResourceManagementAwareApplicationInterface extends ApplicationInterface
{
    public function resourceManagementPlan(): ?ResourceManagementPlan;

    public function resourcePathResolver(): ?ResourcePathResolverInterface;
}
