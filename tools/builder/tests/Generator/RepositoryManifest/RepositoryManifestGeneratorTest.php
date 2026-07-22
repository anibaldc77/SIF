<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\RepositoryManifest;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestGenerator;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryManifestGeneratorTest extends TestCase
{
    public function testGeneratesVersionedRepositoryManifestArtifact(): void
    {
        $index = new RepositoryIndex();
        $source = $this->entry('WP-105');
        $target = $this->entry('ADR-001');
        $index->add($source);
        $index->add($target);

        $resolution = new ResolutionResult([
            new ResolvedReference(new Reference('WP-105', 'ADR-001'), $target),
        ]);
        $workspace = new RepositoryWorkspace(repositoryIndex: $index, resolution: $resolution);
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: $workspace, outputRoot: 'D:/SIF/build');

        $result = (new RepositoryManifestGenerator())->generate($context);

        self::assertTrue($result->isSuccessful());
        self::assertCount(1, $result->artifacts->all());
        $artifact = $result->artifacts->all()[0];
        self::assertSame('repository.manifest', $artifact->generator);
        self::assertSame('build/repository-manifest.generated.json', $artifact->relativePath);
        self::assertSame('json', $artifact->type);
        self::assertStringContainsString('"documents": 2', $artifact->content);
        self::assertStringContainsString('"entry_fingerprint"', $artifact->content);
    }

    public function testReturnsDiagnosticWhenResolutionIsMissing(): void
    {
        $workspace = new RepositoryWorkspace(repositoryIndex: new RepositoryIndex());
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: $workspace);

        $result = (new RepositoryManifestGenerator())->generate($context);

        self::assertFalse($result->isSuccessful());
        self::assertSame('GENERATOR-104', $result->diagnostics->all()[0]->code);
    }

    private function entry(string $identifier): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry($identifier, $identifier, explode('-', $identifier, 2)[0], 'engineering', 'approved', '1.0.0', 'engineering/' . $identifier . '.md');
    }
}
