<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

enum SkeletonOverwritePolicy: string
{
    case Fail = 'fail';
    case Skip = 'skip';
    case Replace = 'replace';
}
