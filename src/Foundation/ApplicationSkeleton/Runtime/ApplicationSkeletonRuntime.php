<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Runtime;

use Sif\Foundation\ApplicationSkeleton\Bootstrap\ApplicationTemplateBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Contracts\SkeletonFilesystemInterface;
use Sif\Foundation\ApplicationSkeleton\Example\ExampleApplicationBlueprintFactory;
use Sif\Foundation\ApplicationSkeleton\Template\ApplicationCodeTemplateFactory;
use Sif\Foundation\ApplicationSkeleton\Validation\ApplicationSkeletonValidator;

final readonly class ApplicationSkeletonRuntime
{
    public function __construct(
        private ApplicationTemplateBlueprintFactory $templates = new ApplicationTemplateBlueprintFactory(),
        private ApplicationCodeTemplateFactory $codeTemplates = new ApplicationCodeTemplateFactory(),
        ?ExampleApplicationBlueprintFactory $examples = null,
    ) {
        $this->examples = $examples ?? new ExampleApplicationBlueprintFactory(
            $this->templates,
            $this->codeTemplates,
        );
    }

    private ExampleApplicationBlueprintFactory $examples;

    public function templates(): ApplicationTemplateBlueprintFactory
    {
        return $this->templates;
    }

    public function codeTemplates(): ApplicationCodeTemplateFactory
    {
        return $this->codeTemplates;
    }

    public function validator(SkeletonFilesystemInterface $filesystem): ApplicationSkeletonValidator
    {
        return new ApplicationSkeletonValidator($filesystem);
    }

    public function examples(): ExampleApplicationBlueprintFactory
    {
        return $this->examples;
    }

    /** @return array{template_factory: bool, code_template_factory: bool, validator_factory: bool, example_factory: bool} */
    public function summary(): array
    {
        return [
            'template_factory' => true,
            'code_template_factory' => true,
            'validator_factory' => true,
            'example_factory' => true,
        ];
    }
}
