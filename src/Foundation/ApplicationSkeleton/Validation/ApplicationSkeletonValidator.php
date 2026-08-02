<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Validation;

use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonArtifactType;

final readonly class ApplicationSkeletonValidator
{
    public function __construct(private SkeletonFilesystemInterface $filesystem)
    {
    }

    public function validate(SkeletonBlueprint $blueprint): ApplicationSkeletonValidationReport
    {
        $issues = [];

        foreach ($blueprint->artifacts() as $artifact) {
            $path = $artifact->path()->path();
            if (!$this->filesystem->exists($path)) {
                $issues[] = new ApplicationSkeletonValidationIssue(
                    'SKELETON_PATH_MISSING',
                    sprintf('Required skeleton path "%s" is missing.', $path->value()),
                );
                continue;
            }

            if ($artifact->type() === SkeletonArtifactType::File && !$this->filesystem->isFile($path)) {
                $issues[] = new ApplicationSkeletonValidationIssue(
                    'SKELETON_FILE_TYPE_MISMATCH',
                    sprintf('Expected "%s" to be a file.', $path->value()),
                );
                continue;
            }

            if ($artifact->type() === SkeletonArtifactType::Directory && !$this->filesystem->isDirectory($path)) {
                $issues[] = new ApplicationSkeletonValidationIssue(
                    'SKELETON_DIRECTORY_TYPE_MISMATCH',
                    sprintf('Expected "%s" to be a directory.', $path->value()),
                );
                continue;
            }

            if ($artifact->type() === SkeletonArtifactType::File) {
                $content = $this->filesystem->read($path);
                if ($artifact->fingerprint() !== hash('sha256', $content)) {
                    $issues[] = new ApplicationSkeletonValidationIssue(
                        'SKELETON_FINGERPRINT_MISMATCH',
                        sprintf('Generated file "%s" differs from its governed template.', $path->value()),
                    );
                }
            }
        }

        return new ApplicationSkeletonValidationReport($issues);
    }
}
