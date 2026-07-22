<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Cli;

use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyAnalyzer;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class ProfileAwareRepositoryPolicyAnalyzer implements AnalyzerInterface
{
    public function __construct(private ResolvedCliConfigurationStore $store)
    {
    }

    public function id(): string
    {
        return RepositoryPolicyAnalyzer::IDENTIFIER;
    }

    public function analyze(BuilderContext $context): AnalysisResult
    {
        return (new RepositoryPolicyAnalyzer($this->store->current()->policies))->analyze($context);
    }
}
