<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\MetadataCompleteness;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\MetadataCompleteness\MetadataCompletenessAnalyzer;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class MetadataCompletenessAnalyzerTest extends TestCase
{
    public function testReturnsPreconditionDiagnosticWithoutWorkspace(): void
    {
        $result = (new MetadataCompletenessAnalyzer())->analyze(new BuilderContext('run-1', '.', 'default'));

        self::assertFalse($result->isSuccessful());
        self::assertSame('ANALYZER-101', $result->diagnostics->all()[0]->code);
    }

    public function testProducesWarningsForIncompleteRecommendedMetadata(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001.md', ['id' => 'ADR-001']));
        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('ADR-001', 'Decision', 'Governance', 'Architecture Decision Record', 'Approved', '1.0.0', 'engineering/ADR-001.md'));
        $workspace = new RepositoryWorkspace($registry);
        $workspace = $workspace->withIndexing($index, new ReferenceCollection(), new ResolutionResult());
        $context = (new BuilderContext('run-1', '.', 'default'))->withRepositoryWorkspace($workspace);

        $result = (new MetadataCompletenessAnalyzer())->analyze($context);

        self::assertTrue($result->isSuccessful());
        self::assertSame(3, $result->diagnostics->count());
        self::assertSame(MetadataCompletenessAnalyzer::IDENTIFIER, $result->diagnostics->all()[0]->extension);
    }
}
