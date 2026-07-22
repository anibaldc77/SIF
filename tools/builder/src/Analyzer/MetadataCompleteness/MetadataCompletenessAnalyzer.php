<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\MetadataCompleteness;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class MetadataCompletenessAnalyzer implements AnalyzerInterface
{
    public const IDENTIFIER = 'metadata.completeness';

    public function __construct(private MetadataCompletenessInspector $inspector = new MetadataCompletenessInspector())
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
        $index = $workspace?->repositoryIndex() ?? $context->repositoryIndex();

        if ($workspace === null || $registry === null || $index === null) {
            return new AnalysisResult(new DiagnosticCollection([
                new Diagnostic(
                    code: 'ANALYZER-101',
                    severity: DiagnosticSeverity::ERROR,
                    message: 'Metadata completeness analysis requires a repository workspace, metadata registry and repository index.',
                    extension: self::IDENTIFIER,
                    remediation: 'Execute repository discovery and indexing before running this analyzer.',
                ),
            ]));
        }

        $diagnostics = [];
        foreach ($this->inspector->inspect($registry, $index) as $finding) {
            $diagnostics[] = new Diagnostic(
                code: $finding->code,
                severity: $finding->severity,
                message: $finding->message,
                source: $finding->path,
                extension: self::IDENTIFIER,
                context: $finding->context,
                remediation: $finding->remediation,
            );
        }

        return new AnalysisResult(new DiagnosticCollection($diagnostics));
    }
}
