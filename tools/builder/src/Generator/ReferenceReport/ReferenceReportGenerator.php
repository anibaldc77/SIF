<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceReport;

use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\GenerationResult;

final readonly class ReferenceReportGenerator implements GeneratorInterface
{
    public const IDENTIFIER = 'reference.report';
    public const ARTIFACT_PATH = 'engineering/REFERENCES.generated.md';

    public function __construct(
        private ReferenceReportViewFactory $views = new ReferenceReportViewFactory(),
        private ReferenceReportMarkdownRenderer $renderer = new ReferenceReportMarkdownRenderer(),
    ) {
    }

    public function id(): string
    {
        return self::IDENTIFIER;
    }

    public function generate(BuilderContext $context): GenerationResult
    {
        $workspace = $context->repositoryWorkspace();
        if ($workspace === null) {
            return $this->missingInput('Repository workspace is unavailable.');
        }

        if ($workspace->repositoryIndex() === null) {
            return $this->missingInput('Repository index is unavailable.');
        }

        if ($workspace->resolution() === null) {
            return $this->missingInput('Reference resolution result is unavailable.');
        }

        return new GenerationResult(artifacts: [
            new GeneratedArtifact(
                generator: self::IDENTIFIER,
                relativePath: self::ARTIFACT_PATH,
                type: 'markdown',
                content: $this->renderer->render($this->views->create($workspace)),
            ),
        ]);
    }

    private function missingInput(string $message): GenerationResult
    {
        return new GenerationResult(new DiagnosticCollection([
            new Diagnostic(
                code: 'GENERATOR-102',
                severity: DiagnosticSeverity::ERROR,
                message: $message,
                extension: self::IDENTIFIER,
                remediation: 'Run repository discovery, indexing, reference parsing, and resolution before generation.',
            ),
        ]));
    }
}
