<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\RepositoryIndex;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\RepositoryIndex\RepositoryIndexGenerator;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryIndexGeneratorTest extends TestCase
{
    public function testGeneratesExpectedArtifactFromWorkspace(): void
    {
        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('WP-105', 'Built-in Generators', 'WP', 'engineering', 'approved', '1.0.0', 'engineering\\specifications\\WP-105\\EG-025.md', 'WP-105'));
        $workspace = new RepositoryWorkspace(repositoryIndex: $index);
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: $workspace, outputRoot: 'D:/SIF/build');

        $result = (new RepositoryIndexGenerator())->generate($context);

        self::assertTrue($result->isSuccessful());
        self::assertCount(1, $result->artifacts->all());
        $artifact = $result->artifacts->all()[0];
        self::assertSame('repository.index', $artifact->generator);
        self::assertSame('engineering/INDEX.generated.md', $artifact->relativePath);
        self::assertStringContainsString('# Engineering Repository Index', $artifact->content);
        self::assertStringContainsString('[`engineering/specifications/WP-105/EG-025.md`](specifications/WP-105/EG-025.md)', $artifact->content);
    }

    public function testReturnsDiagnosticWhenWorkspaceIsMissing(): void
    {
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING);
        $result = (new RepositoryIndexGenerator())->generate($context);

        self::assertFalse($result->isSuccessful());
        self::assertCount(0, $result->artifacts->all());
        self::assertSame('GENERATOR-101', $result->diagnostics->all()[0]->code);
    }
}
