<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

enum SkeletonGenerationAction: string
{
    case CreateDirectory = 'create-directory';
    case CreateFile = 'create-file';
    case ReplaceFile = 'replace-file';
    case Skip = 'skip';
    case Conflict = 'conflict';
}
