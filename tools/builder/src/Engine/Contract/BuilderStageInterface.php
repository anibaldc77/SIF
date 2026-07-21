<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Contract;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\StageResult;

interface BuilderStageInterface
{
    public function phase(): BuilderPhase;

    public function execute(BuilderContext $context): StageResult;
}
