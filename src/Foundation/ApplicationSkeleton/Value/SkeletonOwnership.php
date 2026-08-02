<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

enum SkeletonOwnership: string
{
    case SkeletonOwned = 'skeleton-owned';
    case UserOwned = 'user-owned';
    case RuntimeOwned = 'runtime-owned';
}
