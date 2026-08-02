<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

enum SkeletonArtifactType: string
{
    case Directory = 'directory';
    case File = 'file';
}
