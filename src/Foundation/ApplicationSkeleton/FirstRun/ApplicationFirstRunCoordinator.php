<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\FirstRun;

use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationExecutor;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonGenerationPlanner;
use Sif\Foundation\ApplicationSkeleton\Validation\ApplicationSkeletonValidator;
use Sif\Foundation\ApplicationSkeleton\Validation\ApplicationSkeletonValidationReport;
use Sif\Foundation\ApplicationSkeleton\Value\FirstRunState;

final readonly class ApplicationFirstRunCoordinator
{
    public function __construct(private SkeletonFilesystemInterface $filesystem)
    {
    }

    public function run(
        SkeletonBlueprint $blueprint,
        ?ApplicationFirstRunAuthorization $authorization = null,
    ): ApplicationFirstRunReport {
        $plan = (new SkeletonGenerationPlanner($this->filesystem))->plan($blueprint);
        if (!$plan->executable()) {
            return new ApplicationFirstRunReport(
                FirstRunState::Failed,
                $plan->fingerprint(),
                false,
                new ApplicationSkeletonValidationReport([]),
            );
        }

        if ($authorization === null || !$authorization->authorizes($plan->fingerprint())) {
            return new ApplicationFirstRunReport(
                FirstRunState::Planned,
                $plan->fingerprint(),
                false,
                new ApplicationSkeletonValidationReport([]),
            );
        }

        (new SkeletonGenerationExecutor($this->filesystem))->execute($plan);
        $validation = (new ApplicationSkeletonValidator($this->filesystem))->validate($blueprint);

        return new ApplicationFirstRunReport(
            $validation->valid() ? FirstRunState::Completed : FirstRunState::Failed,
            $plan->fingerprint(),
            true,
            $validation,
        );
    }
}
