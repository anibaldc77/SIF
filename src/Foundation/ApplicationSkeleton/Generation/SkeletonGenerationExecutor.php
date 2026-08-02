<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Exceptions\ApplicationSkeletonException;

final readonly class SkeletonGenerationExecutor
{
    public function __construct(private SkeletonFilesystemInterface $filesystem)
    {
    }

    public function execute(SkeletonGenerationPlan $plan): SkeletonGenerationPlan
    {
        if (!$plan->executable()) {
            throw new ApplicationSkeletonException('Skeleton generation plan contains conflicts.');
        }

        foreach ($plan->entries() as $entry) {
            $artifact = $entry->artifact();
            $path = $artifact->path()->path();

            match ($entry->action()) {
                SkeletonGenerationAction::CreateDirectory => $this->filesystem->createDirectory($path),
                SkeletonGenerationAction::CreateFile,
                SkeletonGenerationAction::ReplaceFile => $this->filesystem->write(
                    $path,
                    $artifact->content() ?? throw new ApplicationSkeletonException('File artifact content is missing.'),
                ),
                SkeletonGenerationAction::Skip => null,
                SkeletonGenerationAction::Conflict => throw new ApplicationSkeletonException(
                    sprintf('Cannot execute conflicting generation entry "%s".', $path->value()),
                ),
            };
        }

        return $plan;
    }
}
