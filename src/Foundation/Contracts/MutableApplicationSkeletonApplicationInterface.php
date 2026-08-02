<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\ApplicationSkeleton\Runtime\ApplicationSkeletonRuntime;

interface MutableApplicationSkeletonApplicationInterface extends ApplicationSkeletonAwareApplicationInterface
{
    public function setApplicationSkeleton(ApplicationSkeletonRuntime $applicationSkeleton): void;
}
