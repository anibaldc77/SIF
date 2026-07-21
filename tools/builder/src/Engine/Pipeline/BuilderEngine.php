<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline;

use Sif\Builder\Engine\Artifact\ArtifactWriterInterface;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Contract\BuilderEngineInterface;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\Contract\RunIdentifierProviderInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\ExecutionPolicy;
use Sif\Builder\Engine\Extension\AnalyzerRegistry;
use Sif\Builder\Engine\Extension\GeneratorRegistry;
use Sif\Builder\Engine\Pipeline\Stage\AnalyzerStage;
use Sif\Builder\Engine\Pipeline\Stage\GeneratorStage;
use Sif\Builder\Engine\Pipeline\Stage\PhaseStage;
use Throwable;

final readonly class BuilderEngine implements BuilderEngineInterface
{
    public function __construct(
        private AnalyzerRegistry $analyzers,
        private GeneratorRegistry $generators,
        private RunIdentifierProviderInterface $runIdentifiers = new RandomRunIdentifierProvider(),
        private BuilderLifecycle $lifecycle = new BuilderLifecycle(),
        private ?BuilderStageInterface $discoveryStage = null,
        private ?BuilderStageInterface $indexingStage = null,
        private ?ArtifactWriterInterface $artifactWriter = null,
    ) {
    }

    public function run(BuilderRequest $request): BuilderResult
    {
        $completedPhases = [];
        $diagnostics = new DiagnosticCollection();
        $context = BuilderContext::fromRequest($this->runIdentifiers->next(), $request);

        try {
            $this->analyzers->freeze();
            $this->generators->freeze();

            $analyzerSelection = $this->analyzers->select($request->enabledAnalyzers);
            $generatorSelection = $this->generators->select($request->enabledGenerators);
            $diagnostics = $diagnostics
                ->merge($analyzerSelection->diagnostics)
                ->merge($generatorSelection->diagnostics);

            foreach ($this->preAnalysisStages() as $stage) {
                [$context, $diagnostics, $completedPhases] = $this->executeStage(
                    $stage,
                    $context,
                    $diagnostics,
                    $completedPhases,
                );
            }

            [$context, $diagnostics, $completedPhases] = $this->executeStage(
                new AnalyzerStage($analyzerSelection->analyzers),
                $context,
                $diagnostics,
                $completedPhases,
            );

            if ($this->mayGenerate($request->policy, $diagnostics)) {
                [$context, $diagnostics, $completedPhases] = $this->executeStage(
                    new GeneratorStage($generatorSelection->generators, $this->artifactWriter),
                    $context,
                    $diagnostics,
                    $completedPhases,
                );
            }

            [$context, $diagnostics, $completedPhases] = $this->executeStage(
                new PhaseStage(BuilderPhase::FINALIZING),
                $context,
                $diagnostics,
                $completedPhases,
            );

            $this->lifecycle->transition($context->phase, BuilderPhase::COMPLETED);
            $completedPhases[] = BuilderPhase::COMPLETED;

            if ($request->policy === ExecutionPolicy::STRICT && $diagnostics->hasErrors()) {
                return BuilderResult::failed(
                    $completedPhases,
                    'Builder execution completed with errors under strict policy.',
                    $diagnostics,
                );
            }

            return BuilderResult::succeeded($completedPhases, $diagnostics);
        } catch (Throwable $throwable) {
            if ($context->phase !== BuilderPhase::FAILED && !$context->phase->isTerminal()) {
                try {
                    $this->lifecycle->transition($context->phase, BuilderPhase::FAILED);
                    $completedPhases[] = BuilderPhase::FAILED;
                } catch (Throwable) {
                    // Preserve the original engine failure as the primary cause.
                }
            }

            $diagnostics = $diagnostics->with(new Diagnostic(
                code: 'ENGINE-500',
                severity: DiagnosticSeverity::FATAL,
                message: 'Builder pipeline failed unexpectedly.',
                remediation: 'Inspect the retained cause in the in-memory result.',
            ));

            return BuilderResult::failed(
                $completedPhases,
                'Builder pipeline failed unexpectedly.',
                $diagnostics,
                $throwable,
            );
        }
    }

    /** @return list<BuilderStageInterface> */
    private function preAnalysisStages(): array
    {
        return [
            new PhaseStage(BuilderPhase::PREPARING),
            $this->discoveryStage ?? new PhaseStage(BuilderPhase::DISCOVERING),
            $this->indexingStage ?? new PhaseStage(BuilderPhase::INDEXING),
        ];
    }

    /**
     * @param list<BuilderPhase> $completedPhases
     * @return array{BuilderContext, DiagnosticCollection, list<BuilderPhase>}
     */
    private function executeStage(
        BuilderStageInterface $stage,
        BuilderContext $context,
        DiagnosticCollection $diagnostics,
        array $completedPhases,
    ): array {
        $this->lifecycle->transition($context->phase, $stage->phase());
        $result = $stage->execute($context);

        if ($result->context->phase !== $stage->phase()) {
            throw new \LogicException(sprintf(
                'Stage "%s" returned context in phase "%s".',
                $stage->phase()->value,
                $result->context->phase->value,
            ));
        }

        $completedPhases[] = $stage->phase();

        return [
            $result->context,
            $diagnostics->merge($result->diagnostics),
            $completedPhases,
        ];
    }

    private function mayGenerate(ExecutionPolicy $policy, DiagnosticCollection $diagnostics): bool
    {
        if ($diagnostics->hasSeverity(DiagnosticSeverity::FATAL)) {
            return false;
        }

        return $policy === ExecutionPolicy::LENIENT || !$diagnostics->hasErrors();
    }
}
