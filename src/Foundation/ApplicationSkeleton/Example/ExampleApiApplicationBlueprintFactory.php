<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Example;

use Sif\Foundation\ApplicationSkeleton\Bootstrap\ApplicationTemplateBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Template\ControllerApiTemplateFactory;

final readonly class ExampleApiApplicationBlueprintFactory
{
    public function __construct(
        private ApplicationTemplateBlueprintFactory $applicationFactory = new ApplicationTemplateBlueprintFactory(),
        private ControllerApiTemplateFactory $controllerFactory = new ControllerApiTemplateFactory(),
    ) {
    }

    public function create(ProjectManifest $manifest): SkeletonBlueprint
    {
        $base = $this->applicationFactory->create($manifest);

        return new SkeletonBlueprint(
            $manifest,
            [...$base->artifacts(), ...$this->controllerFactory->artifacts($manifest)],
        );
    }
}
