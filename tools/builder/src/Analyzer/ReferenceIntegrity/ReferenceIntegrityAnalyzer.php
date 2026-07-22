<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\ReferenceIntegrity;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class ReferenceIntegrityAnalyzer implements AnalyzerInterface
{
    public const IDENTIFIER = 'reference.integrity';

    public function __construct(private ReferenceIntegrityInspector $inspector = new ReferenceIntegrityInspector())
    {
    }

    public function id(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(BuilderContext $context): AnalysisResult
    {
        $workspace = $context->repositoryWorkspace();
        $index = $workspace?->repositoryIndex() ?? $context->repositoryIndex();
        $resolution = $workspace?->resolution();

        if ($workspace === null || $index === null || $resolution === null) {
            return new AnalysisResult(new DiagnosticCollection([
                new Diagnostic(
                    code: 'ANALYZER-102',
                    severity: DiagnosticSeverity::ERROR,
                    message: 'Reference integrity analysis requires a repository workspace, repository index and resolution result.',
                    extension: self::IDENTIFIER,
                    remediation: 'Execute repository discovery, indexing and reference resolution before running this analyzer.',
                ),
            ]));
        }

        $diagnostics = [];
        foreach ($this->inspector->inspect($index, $resolution) as $finding) {
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
