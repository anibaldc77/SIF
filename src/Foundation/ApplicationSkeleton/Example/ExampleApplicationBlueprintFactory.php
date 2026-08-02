<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Example;

use Sif\Foundation\ApplicationSkeleton\Bootstrap\ApplicationTemplateBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Generation\SkeletonBlueprint;
use Sif\Foundation\ApplicationSkeleton\Manifest\ProjectManifest;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationCodeTemplateFactory;
use Sif\Foundation\ApplicationSkeleton\Template\ModelTemplateOptions;
use Sif\Foundation\ApplicationSkeleton\Value\ApplicationCodeName;
use Sif\Foundation\ApplicationSkeleton\Value\MigrationTemplateName;

final readonly class ExampleApplicationBlueprintFactory
{
    public function __construct(
        private ApplicationTemplateBlueprintFactory $applicationFactory = new ApplicationTemplateBlueprintFactory(),
        private ApplicationCodeTemplateFactory $codeFactory = new ApplicationCodeTemplateFactory(),
    ) {
    }

    public function create(ProjectManifest $manifest): SkeletonBlueprint
    {
        $base = $this->applicationFactory->create($manifest);
        $artifacts = $base->artifacts();
        $artifacts[] = $this->codeFactory->moduleServiceProvider($manifest, new ApplicationCodeName('Welcome'));
        $artifacts[] = $this->codeFactory->model(
            $manifest,
            new ApplicationCodeName('WelcomeMessage'),
            'welcome_messages',
            new ModelTemplateOptions(['id'], true, false),
        );
        $artifacts[] = $this->codeFactory->migration(
            $manifest,
            new MigrationTemplateName('202608020001_create_welcome_messages'),
            new ApplicationCodeName('CreateWelcomeMessages'),
        );

        return new SkeletonBlueprint($manifest, $artifacts);
    }
}
