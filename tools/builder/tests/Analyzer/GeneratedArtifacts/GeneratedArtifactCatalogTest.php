<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\GeneratedArtifacts;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactCatalog;
use Sif\Builder\Analyzer\GeneratedArtifacts\GeneratedArtifactDefinition;

final class GeneratedArtifactCatalogTest extends TestCase
{
    public function testBuiltInCatalogIsOrderedAndComplete(): void
    {
        self::assertSame([
            'build/reference-graph.generated.json',
            'build/repository-manifest.generated.json',
            'engineering/INDEX.generated.md',
            'engineering/NAVIGATION.generated.md',
            'engineering/REFERENCES.generated.md',
        ], GeneratedArtifactCatalog::builtIn()->paths());
    }

    public function testRejectsDuplicatePaths(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GeneratedArtifactCatalog([
            new GeneratedArtifactDefinition('one', 'build/report.generated.json'),
            new GeneratedArtifactDefinition('two', 'build/report.generated.json'),
        ]);
    }
}
