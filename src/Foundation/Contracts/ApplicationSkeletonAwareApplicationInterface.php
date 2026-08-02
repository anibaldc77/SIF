<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\ApplicationSkeleton\Runtime\ApplicationSkeletonRuntime;

interface ApplicationSkeletonAwareApplicationInterface extends ApplicationInterface
{
    public function applicationSkeleton(): ?ApplicationSkeletonRuntime;
}
