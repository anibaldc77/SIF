<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Runtime;

use Sif\Builder\Cli\Contract\BuilderEngineFactoryInterface;
use Sif\Builder\Engine\Artifact\ArtifactWriterInterface;
use Sif\Builder\Engine\Contract\BuilderEngineInterface;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\Contract\RunIdentifierProviderInterface;
use Sif\Builder\Engine\Extension\AnalyzerRegistry;
use Sif\Builder\Engine\Extension\GeneratorRegistry;
use Sif\Builder\Engine\Pipeline\BuilderEngine;
use Sif\Builder\Engine\Pipeline\BuilderLifecycle;
use Sif\Builder\Engine\Pipeline\RandomRunIdentifierProvider;

final readonly class DefaultBuilderEngineFactory implements BuilderEngineFactoryInterface
{
    public function __construct(
        private AnalyzerRegistry $analyzers,
        private GeneratorRegistry $generators,
        private ?BuilderStageInterface $discoveryStage = null,
        private ?BuilderStageInterface $indexingStage = null,
        private ?ArtifactWriterInterface $artifactWriter = null,
        private RunIdentifierProviderInterface $runIdentifiers = new RandomRunIdentifierProvider(),
        private BuilderLifecycle $lifecycle = new BuilderLifecycle(),
    ) {
    }

    public function create(EngineExecutionMode $mode): BuilderEngineInterface
    {
        return new BuilderEngine(
            analyzers: $this->analyzers,
            generators: $mode === EngineExecutionMode::BUILD ? $this->generators : new GeneratorRegistry(),
            runIdentifiers: $this->runIdentifiers,
            lifecycle: $this->lifecycle,
            discoveryStage: $this->discoveryStage,
            indexingStage: $this->indexingStage,
            artifactWriter: $mode === EngineExecutionMode::BUILD ? $this->artifactWriter : null,
        );
    }
}
