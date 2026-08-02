<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Value\SkeletonOverwritePolicy;

final readonly class SkeletonGenerationPlanner
{
    public function __construct(private SkeletonFilesystemInterface $filesystem)
    {
    }

    public function plan(SkeletonBlueprint $blueprint): SkeletonGenerationPlan
    {
        $entries = [];

        foreach ($blueprint->artifacts() as $artifact) {
            $path = $artifact->path()->path();

            if (!$this->filesystem->exists($path)) {
                $entries[] = new SkeletonGenerationEntry(
                    $artifact,
                    $artifact->type() === SkeletonArtifactType::Directory
                        ? SkeletonGenerationAction::CreateDirectory
                        : SkeletonGenerationAction::CreateFile,
                );
                continue;
            }

            if ($artifact->type() === SkeletonArtifactType::Directory) {
                $entries[] = $this->filesystem->isDirectory($path)
                    ? new SkeletonGenerationEntry($artifact, SkeletonGenerationAction::Skip, reason: 'directory-exists')
                    : new SkeletonGenerationEntry($artifact, SkeletonGenerationAction::Conflict, reason: 'file-blocks-directory');
                continue;
            }

            if (!$this->filesystem->isFile($path)) {
                $entries[] = new SkeletonGenerationEntry(
                    $artifact,
                    SkeletonGenerationAction::Conflict,
                    reason: 'directory-blocks-file',
                );
                continue;
            }

            $currentFingerprint = hash('sha256', $this->filesystem->read($path));
            if ($currentFingerprint === $artifact->fingerprint()) {
                $entries[] = new SkeletonGenerationEntry(
                    $artifact,
                    SkeletonGenerationAction::Skip,
                    $currentFingerprint,
                    'content-unchanged',
                );
                continue;
            }

            $policy = $artifact->path()->overwritePolicy();
            $entries[] = match ($policy) {
                SkeletonOverwritePolicy::Skip => new SkeletonGenerationEntry(
                    $artifact,
                    SkeletonGenerationAction::Skip,
                    $currentFingerprint,
                    'overwrite-policy-skip',
                ),
                SkeletonOverwritePolicy::Replace => new SkeletonGenerationEntry(
                    $artifact,
                    SkeletonGenerationAction::ReplaceFile,
                    $currentFingerprint,
                    'skeleton-owned-replacement',
                ),
                SkeletonOverwritePolicy::Fail => new SkeletonGenerationEntry(
                    $artifact,
                    SkeletonGenerationAction::Conflict,
                    $currentFingerprint,
                    'existing-content-differs',
                ),
            };
        }

        return new SkeletonGenerationPlan($entries);
    }
}
