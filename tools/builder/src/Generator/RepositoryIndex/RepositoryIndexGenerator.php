<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryIndex;

use Sif\Builder\Engine\Artifact\GeneratedArtifact;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\GenerationResult;

final readonly class RepositoryIndexGenerator implements GeneratorInterface
{
    public const IDENTIFIER = 'repository.index';
    public const ARTIFACT_PATH = 'engineering/INDEX.generated.md';

    public function __construct(
        private RepositoryIndexViewFactory $views = new RepositoryIndexViewFactory(),
        private RepositoryIndexMarkdownRenderer $renderer = new RepositoryIndexMarkdownRenderer(),
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

        $view = $this->views->create($workspace);

        return new GenerationResult(artifacts: [
            new GeneratedArtifact(
                generator: self::IDENTIFIER,
                relativePath: self::ARTIFACT_PATH,
                type: 'markdown',
                content: $this->renderer->render($view),
            ),
        ]);
    }

    private function missingInput(string $message): GenerationResult
    {
        return new GenerationResult(new DiagnosticCollection([
            new Diagnostic(
                code: 'GENERATOR-101',
                severity: DiagnosticSeverity::ERROR,
                message: $message,
                extension: self::IDENTIFIER,
                remediation: 'Run repository discovery and indexing before artifact generation.',
            ),
        ]));
    }
}
