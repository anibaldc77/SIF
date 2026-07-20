<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Graph;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Graph\ReferenceGraph;
use Sif\Builder\Reference\Graph\ReferenceImpactAnalyzer;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceImpactAnalyzerTest extends TestCase
{
    public function testFindsDirectAndTransitiveDependents(): void
    {
        $graph = ReferenceGraph::fromResolution(new ResolutionResult([
            $this->resolved('WP-101', 'ADR-001'),
            $this->resolved('WP-102', 'ADR-001'),
            $this->resolved('SPEC-001', 'WP-101'),
            $this->resolved('DOC-001', 'SPEC-001'),
        ]));
        $analyzer = new ReferenceImpactAnalyzer();

        self::assertSame(['WP-101', 'WP-102'], $analyzer->directDependents($graph, 'ADR-001'));
        self::assertSame(['DOC-001', 'SPEC-001', 'WP-101', 'WP-102'], $analyzer->transitiveDependents($graph, 'ADR-001'));
        self::assertSame([], $analyzer->transitiveDependents($graph, 'UNKNOWN'));
    }

    private function resolved(string $source, string $target): ResolvedReference
    {
        return new ResolvedReference(new Reference($source, $target), new RepositoryIndexEntry($target, $target, 'Document', 'Engineering', 'Draft', '0.1.0', "/repo/$target.md", null));
    }
}
