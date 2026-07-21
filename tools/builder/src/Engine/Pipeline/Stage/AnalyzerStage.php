<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline\Stage;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Contract\AnalyzerInterface;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\StageResult;
use Throwable;

final readonly class AnalyzerStage implements BuilderStageInterface
{
    /** @var list<AnalyzerInterface> */
    private array $analyzers;

    /** @param list<AnalyzerInterface> $analyzers */
    public function __construct(array $analyzers)
    {
        $this->analyzers = array_values($analyzers);
    }

    public function phase(): BuilderPhase
    {
        return BuilderPhase::ANALYZING;
    }

    public function execute(BuilderContext $context): StageResult
    {
        $context = $context->withPhase($this->phase());
        $diagnostics = new DiagnosticCollection();

        foreach ($this->analyzers as $analyzer) {
            try {
                $diagnostics = $diagnostics->merge($analyzer->analyze($context)->diagnostics);
            } catch (Throwable) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'ANALYZER-500',
                    severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Analyzer "%s" failed unexpectedly.', $analyzer->id()),
                    extension: $analyzer->id(),
                    remediation: 'Inspect the analyzer implementation and rerun the builder.',
                ));
            }
        }

        return new StageResult($context, $diagnostics);
    }
}
