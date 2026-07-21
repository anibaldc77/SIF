<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline\Stage;

use LogicException;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Contract\BuilderStageInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\StageResult;
use Sif\Builder\Reference\Exception\DuplicateReferenceException;
use Sif\Builder\Reference\Exception\ReferenceParseException;
use Sif\Builder\Reference\Parser\ReferenceParserInterface;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\Resolution\ReferenceResolverInterface;
use Sif\Builder\Repository\RepositoryIndexBuilder;

final readonly class RepositoryIndexingStage implements BuilderStageInterface
{
    public function __construct(
        private RepositoryIndexBuilder $indexBuilder,
        private ReferenceParserInterface $referenceParser,
        private ReferenceResolverInterface $referenceResolver,
    ) {
    }

    public function phase(): BuilderPhase
    {
        return BuilderPhase::INDEXING;
    }

    public function execute(BuilderContext $context): StageResult
    {
        $workspace = $context->repositoryWorkspace();
        $registry = $workspace?->metadataRegistry();

        if ($workspace === null || $registry === null) {
            throw new LogicException('Repository indexing requires a completed discovery stage.');
        }

        $index = $this->indexBuilder->build($registry);
        $references = new ReferenceCollection();
        $diagnostics = new DiagnosticCollection();

        foreach ($registry->all() as $document) {
            try {
                foreach ($this->referenceParser->parse($document)->all() as $reference) {
                    $references->add($reference);
                }
            } catch (ReferenceParseException|DuplicateReferenceException $exception) {
                $diagnostics = $diagnostics->with(new Diagnostic(
                    code: 'REFERENCE-201',
                    severity: DiagnosticSeverity::ERROR,
                    message: $exception->getMessage(),
                    source: $document->path,
                    remediation: 'Correct the reference metadata and run the builder again.',
                ));
            }
        }

        $resolution = $this->referenceResolver->resolve($references, $index);

        foreach ($resolution->broken as $broken) {
            $diagnostics = $diagnostics->with(new Diagnostic(
                code: 'REFERENCE-404',
                severity: DiagnosticSeverity::ERROR,
                message: sprintf(
                    'Reference target "%s" from "%s" was not found.',
                    $broken->reference->targetIdentifier,
                    $broken->reference->sourceIdentifier,
                ),
                source: $broken->reference->sourceIdentifier,
                context: [
                    'target' => $broken->reference->targetIdentifier,
                    'type' => $broken->reference->type->value,
                ],
                remediation: 'Create the referenced document or correct the target identifier.',
            ));
        }

        $configuration = array_merge($context->configuration, [
            'repository.documents' => $index->count(),
            'repository.references' => $references->count(),
            'repository.references_broken' => $resolution->brokenCount(),
            'repository.references_resolved' => $resolution->resolvedCount(),
        ]);

        $workspace = $workspace->withIndexing($index, $references, $resolution);

        return new StageResult(
            $context
                ->withPhase(BuilderPhase::INDEXING)
                ->withRepositoryWorkspace($workspace)
                ->withConfiguration($configuration),
            $diagnostics,
        );
    }
}
