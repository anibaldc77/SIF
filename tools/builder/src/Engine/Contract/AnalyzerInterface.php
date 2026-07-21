<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Contract;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Extension\AnalysisResult;

interface AnalyzerInterface
{
    public function id(): string;

    public function analyze(BuilderContext $context): AnalysisResult;
}
