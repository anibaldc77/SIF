<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\ReferenceReport;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\ReferenceReport\ReferenceReportGenerator;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceReportGeneratorTest extends TestCase
{
    public function testGeneratesReferenceReportArtifact(): void
    {
        $index = new RepositoryIndex();
        $source = $this->entry('WP-105', 'Built-in Generators');
        $target = $this->entry('ADR-001', 'Generator Contracts');
        $isolated = $this->entry('RFC-002', 'Future RFC');
        $index->add($source);
        $index->add($target);
        $index->add($isolated);

        $resolution = new ResolutionResult(
            resolved: [new ResolvedReference(new Reference('WP-105', 'ADR-001', ReferenceType::IMPLEMENTS, 14), $target)],
            broken: [new BrokenReference(new Reference('WP-105', 'SPEC-999', ReferenceType::REFERENCE, 20))],
        );
        $workspace = new RepositoryWorkspace(repositoryIndex: $index, resolution: $resolution);
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: $workspace, outputRoot: 'D:/SIF/build');

        $result = (new ReferenceReportGenerator())->generate($context);

        self::assertTrue($result->isSuccessful());
        self::assertCount(1, $result->artifacts->all());
        $artifact = $result->artifacts->all()[0];
        self::assertSame('reference.report', $artifact->generator);
        self::assertSame('engineering/REFERENCES.generated.md', $artifact->relativePath);
        self::assertStringContainsString('# Repository Reference Report', $artifact->content);
        self::assertStringContainsString('`SPEC-999`', $artifact->content);
        self::assertStringContainsString('`RFC-002` — Future RFC', $artifact->content);
    }

    public function testReturnsDiagnosticWhenResolutionIsMissing(): void
    {
        $index = new RepositoryIndex();
        $workspace = new RepositoryWorkspace(repositoryIndex: $index);
        $context = new BuilderContext('run-1', 'D:/SIF', 'default', BuilderPhase::GENERATING, repositoryWorkspace: $workspace);

        $result = (new ReferenceReportGenerator())->generate($context);

        self::assertFalse($result->isSuccessful());
        self::assertSame('GENERATOR-102', $result->diagnostics->all()[0]->code);
    }

    private function entry(string $identifier, string $title): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry($identifier, $title, explode('-', $identifier, 2)[0], 'engineering', 'approved', '1.0.0', 'engineering/' . $identifier . '.md');
    }
}
