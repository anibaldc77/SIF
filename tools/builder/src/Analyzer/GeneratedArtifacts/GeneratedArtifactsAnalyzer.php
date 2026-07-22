<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\GeneratedArtifacts;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\AnalysisResult;

final readonly class GeneratedArtifactsAnalyzer implements AnalyzerInterface
{
    public const IDENTIFIER = 'generated.artifacts';

    public function __construct(
        private GeneratedArtifactCatalog $catalog = new GeneratedArtifactCatalog(),
        private GeneratedArtifactsInspector $inspector = new GeneratedArtifactsInspector(),
    ) {
    }

    public static function builtIn(): self
    {
        return new self(GeneratedArtifactCatalog::builtIn());
    }

    public function id(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(BuilderContext $context): AnalysisResult
    {
        $workspace = $context->repositoryWorkspace();
        $registry = $workspace?->metadataRegistry();
        if ($workspace === null || $registry === null || !is_dir($context->repositoryRoot)) {
            return new AnalysisResult(new DiagnosticCollection([
                new Diagnostic(
                    code: 'ANALYZER-105',
                    severity: DiagnosticSeverity::ERROR,
                    message: 'Generated artifact analysis requires an accessible repository and metadata registry.',
                    extension: self::IDENTIFIER,
                    remediation: 'Execute repository discovery against an accessible repository before running this analyzer.',
                ),
            ]));
        }

        $diagnostics = [];
        foreach ($this->inspector->inspect(
            $context->repositoryRoot,
            $context->outputRoot ?? $context->repositoryRoot,
            $registry,
            $this->catalog,
        ) as $finding) {
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
