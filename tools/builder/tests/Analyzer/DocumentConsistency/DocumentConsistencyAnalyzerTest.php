<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\DocumentConsistency;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\DocumentConsistency\DocumentConsistencyAnalyzer;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final class DocumentConsistencyAnalyzerTest extends TestCase
{
    public function testReturnsPreconditionDiagnosticWithoutWorkspace(): void
    {
        $result = (new DocumentConsistencyAnalyzer())->analyze(new BuilderContext('run-1', '.', 'default'));

        self::assertFalse($result->isSuccessful());
        self::assertSame('ANALYZER-103', $result->diagnostics->all()[0]->code);
    }

    public function testPublishesInspectorFindingsAsDiagnostics(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001.md', [
            'id' => 'ADR-001',
            'status' => 'Approved',
            'version' => '1',
            'category' => 'Architecture Decision Record',
            'document_class' => 'GovernanceDocument',
            'created' => '2026-07-22',
            'updated' => '2026-07-22',
        ]));
        $workspace = (new RepositoryWorkspace())->withMetadataRegistry($registry);
        $context = (new BuilderContext('run-1', '.', 'default'))->withRepositoryWorkspace($workspace);

        $result = (new DocumentConsistencyAnalyzer())->analyze($context);

        self::assertFalse($result->isSuccessful());
        self::assertSame('DOCCONS-202', $result->diagnostics->all()[0]->code);
        self::assertSame(DocumentConsistencyAnalyzer::IDENTIFIER, $result->diagnostics->all()[0]->extension);
        self::assertSame('engineering/ADR-001.md', $result->diagnostics->all()[0]->source);
    }
}
