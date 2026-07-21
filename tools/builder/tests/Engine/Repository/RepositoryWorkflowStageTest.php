<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Pipeline\Stage\RepositoryDiscoveryStage;
use Sif\Builder\Engine\Pipeline\Stage\RepositoryIndexingStage;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Metadata\MetadataScanIssue;
use Sif\Builder\Metadata\MetadataScanResult;
use Sif\Builder\Reference\Parser\FrontMatterReferenceParser;
use Sif\Builder\Reference\Resolution\ReferenceResolver;
use Sif\Builder\Repository\RepositoryIndexBuilder;
use Sif\Builder\Tests\Engine\Repository\Fixtures\InMemoryRepositoryScanner;

final class RepositoryWorkflowStageTest extends TestCase
{
    public function testDiscoveryAndIndexingBuildWorkspaceDeterministically(): void
    {
        $registry = new MetadataRegistry();
        $registry->register($this->document('ADR-001', []));
        $registry->register($this->document('WP-103', ['references' => ['ADR-001']]));

        $discovery = new RepositoryDiscoveryStage(new InMemoryRepositoryScanner(
            new MetadataScanResult($registry, []),
        ));
        $indexing = new RepositoryIndexingStage(
            new RepositoryIndexBuilder(),
            new FrontMatterReferenceParser(),
            new ReferenceResolver(),
        );

        $context = new BuilderContext('run-001', 'D:/SIF', 'default');
        $discovered = $discovery->execute($context);
        $indexed = $indexing->execute($discovered->context);
        $workspace = $indexed->context->repositoryWorkspace();

        self::assertSame(BuilderPhase::DISCOVERING, $discovered->context->phase);
        self::assertSame(BuilderPhase::INDEXING, $indexed->context->phase);
        self::assertNotNull($workspace);
        self::assertSame(2, $workspace->repositoryIndex()?->count());
        self::assertSame(1, $workspace->references()?->count());
        self::assertSame(1, $workspace->resolution()?->resolvedCount());
        self::assertSame(0, $workspace->resolution()?->brokenCount());
        self::assertSame(2, $indexed->context->configuration['repository.documents']);
        self::assertFalse($indexed->diagnostics->hasErrors());
    }

    public function testDiscoveryIssuesBecomeDiagnostics(): void
    {
        $stage = new RepositoryDiscoveryStage(new InMemoryRepositoryScanner(
            new MetadataScanResult(new MetadataRegistry(), [
                new MetadataScanIssue('engineering/broken.md', 'Invalid front matter.'),
            ]),
        ));

        $result = $stage->execute(new BuilderContext('run-002', 'D:/SIF', 'default'));

        self::assertTrue($result->diagnostics->hasErrors());
        self::assertSame('REPOSITORY-101', $result->diagnostics->all()[0]->code);
    }

    public function testBrokenReferencesBecomeDiagnosticsAndRemainInWorkspace(): void
    {
        $registry = new MetadataRegistry();
        $registry->register($this->document('WP-103', ['references' => ['ADR-999']]));
        $discovery = new RepositoryDiscoveryStage(new InMemoryRepositoryScanner(
            new MetadataScanResult($registry, []),
        ));
        $indexing = new RepositoryIndexingStage(
            new RepositoryIndexBuilder(),
            new FrontMatterReferenceParser(),
            new ReferenceResolver(),
        );

        $result = $indexing->execute($discovery->execute(
            new BuilderContext('run-003', 'D:/SIF', 'default'),
        )->context);

        self::assertTrue($result->diagnostics->hasErrors());
        self::assertSame('REFERENCE-404', $result->diagnostics->all()[0]->code);
        self::assertSame(1, $result->context->repositoryWorkspace()?->resolution()?->brokenCount());
    }

    /** @param array<string, mixed> $extra */
    private function document(string $identifier, array $extra): MetadataDocument
    {
        return new MetadataDocument(
            sprintf('engineering/%s.md', $identifier),
            array_merge([
                'id' => $identifier,
                'title' => $identifier,
                'document_class' => 'EngineeringDocument',
                'category' => 'Engineering',
                'status' => 'Draft',
                'version' => '0.1.0',
            ], $extra),
        );
    }
}
