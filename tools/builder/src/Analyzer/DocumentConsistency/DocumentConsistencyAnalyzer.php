<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\DocumentConsistency;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class DocumentConsistencyAnalyzer implements AnalyzerInterface
{
    public const IDENTIFIER = 'document.consistency';

    public function __construct(private DocumentConsistencyInspector $inspector = new DocumentConsistencyInspector())
    {
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
                    code: 'ANALYZER-103',
                    severity: DiagnosticSeverity::ERROR,
                    message: 'Document consistency analysis requires a repository workspace and metadata registry.',
                    extension: self::IDENTIFIER,
                    remediation: 'Execute repository discovery before running this analyzer.',
                ),
            ]));
        }

        $diagnostics = [];
        foreach ($this->inspector->inspect($registry) as $finding) {
            $diagnostics[] = new Diagnostic(
                code: $finding->code,
                severity: $finding->severity,
                message: $finding->message,
                source: $finding->sourcePath,
                extension: self::IDENTIFIER,
                context: $finding->context,
                remediation: $finding->remediation,
            );
        }

        return new AnalysisResult(new DiagnosticCollection($diagnostics));
    }
}
