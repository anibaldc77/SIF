<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Manifest;

use Sif\Foundation\ApplicationSkeleton\Value\ProjectPath;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOwnership;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;

final readonly class ProjectPathDefinition
{
    public function __construct(
        private ProjectPath $path,
        private SkeletonOwnership $ownership,
        private SkeletonOverwritePolicy $overwritePolicy = SkeletonOverwritePolicy::Fail,
    ) {
        if (
            $ownership !== SkeletonOwnership::SkeletonOwned
            && $overwritePolicy === SkeletonOverwritePolicy::Replace
        ) {
            throw new \Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException(
                'Only skeleton-owned paths may use the replace overwrite policy.',
            );
        }
    }

    public function path(): ProjectPath
    {
        return $this->path;
    }

    public function ownership(): SkeletonOwnership
    {
        return $this->ownership;
    }

    public function overwritePolicy(): SkeletonOverwritePolicy
    {
        return $this->overwritePolicy;
    }

    /** @return array{path: string, ownership: string, overwrite_policy: string} */
    public function toArray(): array
    {
        return [
            'path' => $this->path->value(),
            'ownership' => $this->ownership->value,
            'overwrite_policy' => $this->overwritePolicy->value,
        ];
    }
}
