<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\RepositoryPolicy;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class RepositoryPolicyAnalyzer implements AnalyzerInterface
{
    public const IDENTIFIER = 'repository.policy';

    public function __construct(
        private RepositoryPolicySet $policies = new RepositoryPolicySet(),
        private RepositoryPolicyInspector $inspector = new RepositoryPolicyInspector(),
    ) {
    }

    public function id(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(BuilderContext $context): AnalysisResult
    {
        $workspace = $context->repositoryWorkspace();
        $registry = $workspace?->metadataRegistry();

        if ($workspace === null || $registry === null) {
            return new AnalysisResult(new DiagnosticCollection([
                new Diagnostic(
                    code: 'ANALYZER-104',
                    severity: DiagnosticSeverity::ERROR,
                    message: 'Repository policy analysis requires a repository workspace and metadata registry.',
                    extension: self::IDENTIFIER,
                    remediation: 'Execute repository discovery before running this analyzer.',
                ),
            ]));
        }

        $diagnostics = [];
        foreach ($this->inspector->inspect($registry, $this->policies) as $finding) {
            $diagnostics[] = new Diagnostic(
                code: $finding->code,
                severity: $finding->severity,
                message: $finding->message,
                source: $finding->sourcePath,
                extension: self::IDENTIFIER,
                context: ['rule_id' => $finding->ruleId] + $finding->context,
                remediation: $finding->remediation,
            );
        }

        return new AnalysisResult(new DiagnosticCollection($diagnostics));
    }
}
