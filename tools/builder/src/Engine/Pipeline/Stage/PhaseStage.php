<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline\Stage;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\StageResult;

final readonly class PhaseStage implements BuilderStageInterface
{
    public function __construct(private BuilderPhase $builderPhase)
    {
    }

    public function phase(): BuilderPhase
    {
        return $this->builderPhase;
    }

    public function execute(BuilderContext $context): StageResult
    {
        return new StageResult($context->withPhase($this->builderPhase));
    }
}
