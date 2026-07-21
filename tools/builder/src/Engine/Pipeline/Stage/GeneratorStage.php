<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline\Stage;

use Sif\Builder\Engine\Artifact\ArtifactCollection;
use Sif\Builder\Engine\Artifact\ArtifactWriterInterface;
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
    public function __construct(array $generators, private ?ArtifactWriterInterface $writer = null)
    {
        $this->generators = array_values($generators);
    }

    public function phase(): BuilderPhase { return BuilderPhase::GENERATING; }

    public function execute(BuilderContext $context): StageResult
    {
        $context = $context->withPhase($this->phase());
        $diagnostics = new DiagnosticCollection();
        $artifacts = new ArtifactCollection();

        foreach ($this->generators as $generator) {
            try {
                $result = $generator->generate($context);
                $diagnostics = $diagnostics->merge($result->diagnostics);
                foreach ($result->artifacts as $artifact) {
                    $artifacts->add($artifact);
                }
            } catch (Throwable) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'GENERATOR-500', severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Generator "%s" failed unexpectedly.', $generator->id()),
                    extension: $generator->id(), remediation: 'Inspect the generator implementation and rerun the builder.',
                ));
            }
        }

        if ($this->writer !== null && count($artifacts) > 0) {
            if ($context->outputRoot === null) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'ARTIFACT-101', severity: DiagnosticSeverity::ERROR,
                    message: 'Generated artifacts require an approved output root.',
                    remediation: 'Set BuilderRequest::outputRoot before enabling generators.',
                ));
            } else {
                foreach ($artifacts as $artifact) {
                    try { $this->writer->write($context->outputRoot, $artifact); }
                    catch (Throwable) {
                        $diagnostics = $diagnostics->with(new Diagnostic(
                            code: 'ARTIFACT-500', severity: DiagnosticSeverity::ERROR,
                            message: sprintf('Artifact "%s" could not be written.', $artifact->relativePath),
                            extension: $artifact->generator,
                            remediation: 'Verify output permissions and available storage.',
                        ));
                    }
                }
            }
        }

        return new StageResult($context, $diagnostics, $artifacts);
    }
}
