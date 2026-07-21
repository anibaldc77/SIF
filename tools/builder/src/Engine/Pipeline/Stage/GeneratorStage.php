<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline\Stage;

use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\StageResult;
use Throwable;

final readonly class GeneratorStage implements BuilderStageInterface
{
    /** @var list<GeneratorInterface> */
    private array $generators;

    /** @param list<GeneratorInterface> $generators */
    public function __construct(array $generators)
    {
        $this->generators = array_values($generators);
    }

    public function phase(): BuilderPhase
    {
        return BuilderPhase::GENERATING;
    }

    public function execute(BuilderContext $context): StageResult
    {
        $context = $context->withPhase($this->phase());
        $diagnostics = new DiagnosticCollection();

        foreach ($this->generators as $generator) {
            try {
                $diagnostics = $diagnostics->merge($generator->generate($context)->diagnostics);
            } catch (Throwable) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'GENERATOR-500',
                    severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Generator "%s" failed unexpectedly.', $generator->id()),
                    extension: $generator->id(),
                    remediation: 'Inspect the generator implementation and rerun the builder.',
                ));
            }
        }

        return new StageResult($context, $diagnostics);
    }
}
