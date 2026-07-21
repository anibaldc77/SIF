<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline\Stage;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Engine\StageResult;
use Sif\Builder\Metadata\RepositoryScannerInterface;

final readonly class RepositoryDiscoveryStage implements BuilderStageInterface
{
    public function __construct(private RepositoryScannerInterface $scanner)
    {
    }

    public function phase(): BuilderPhase
    {
        return BuilderPhase::DISCOVERING;
    }

    public function execute(BuilderContext $context): StageResult
    {
        $scan = $this->scanner->scan($context->repositoryRoot);
        $diagnostics = new DiagnosticCollection();

        foreach ($scan->issues as $issue) {
            $diagnostics = $diagnostics->with(new Diagnostic(
                code: 'REPOSITORY-101',
                severity: DiagnosticSeverity::ERROR,
                message: $issue->message,
                source: $issue->path,
                remediation: 'Correct the document metadata and run the builder again.',
            ));
        }

        $workspace = ($context->repositoryWorkspace() ?? new RepositoryWorkspace())
            ->withMetadataRegistry($scan->registry);

        return new StageResult(
            $context
                ->withPhase(BuilderPhase::DISCOVERING)
                ->withRepositoryWorkspace($workspace),
            $diagnostics,
        );
    }
}
