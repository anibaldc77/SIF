<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\DocumentationNavigation;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\DocumentationNavigation\DocumentationNavigationGenerator;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class DocumentationNavigationGeneratorTest extends TestCase
{
    public function testGeneratesDocumentationNavigationArtifact(): void
    {
        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('ADR-001', 'Architecture', 'ADR', 'Architecture', 'approved', '1.0.0', 'engineering/ADR-001.md'));
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: new RepositoryWorkspace(repositoryIndex: $index));

        $result = (new DocumentationNavigationGenerator())->generate($context);

        self::assertTrue($result->isSuccessful());
        self::assertCount(1, $result->artifacts->all());
        $artifact = $result->artifacts->all()[0];
        self::assertSame('documentation.navigation', $artifact->generator);
        self::assertSame('engineering/NAVIGATION.generated.md', $artifact->relativePath);
        self::assertSame('markdown', $artifact->type);
    }

    public function testReturnsDiagnosticWhenIndexIsMissing(): void
    {
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: new RepositoryWorkspace());
        $result = (new DocumentationNavigationGenerator())->generate($context);
        self::assertFalse($result->isSuccessful());
        self::assertSame('GENERATOR-105', $result->diagnostics->all()[0]->code);
    }
}
