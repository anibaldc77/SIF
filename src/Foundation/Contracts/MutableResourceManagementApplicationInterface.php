<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Resources\Contracts\ResourcePathResolverInterface;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;

interface MutableResourceManagementApplicationInterface extends ResourceManagementAwareApplicationInterface
{
    public function setResourceManagement(
        ResourceManagementPlan $plan,
        ResourcePathResolverInterface $resolver,
    ): void;
}
