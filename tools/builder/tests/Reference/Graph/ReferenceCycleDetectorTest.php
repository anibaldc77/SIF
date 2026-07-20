<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Graph;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Graph\ReferenceCycleDetector;
use Sif\Builder\Reference\Graph\ReferenceGraph;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceCycleDetectorTest extends TestCase
{
    public function testDetectsAndDeduplicatesCyclesDeterministically(): void
    {
        $graph = ReferenceGraph::fromResolution(new ResolutionResult([
            $this->resolved('ADR-001', 'WP-100'),
            $this->resolved('WP-100', 'SPEC-001'),
            $this->resolved('SPEC-001', 'ADR-001'),
            $this->resolved('SELF-001', 'SELF-001'),
        ]));

        $cycles = (new ReferenceCycleDetector())->detect($graph);

        self::assertCount(2, $cycles);
        self::assertSame('ADR-001->WP-100->SPEC-001->ADR-001', $cycles[0]->identity());
        self::assertSame('SELF-001->SELF-001', $cycles[1]->identity());
    }

    public function testReturnsEmptyListForAcyclicGraph(): void
    {
        $graph = ReferenceGraph::fromResolution(new ResolutionResult([$this->resolved('A-001', 'B-001')]));
        self::assertSame([], (new ReferenceCycleDetector())->detect($graph));
    }

    private function resolved(string $source, string $target): ResolvedReference
    {
        return new ResolvedReference(new Reference($source, $target), new RepositoryIndexEntry($target, $target, 'Document', 'Engineering', 'Draft', '0.1.0', "/repo/$target.md", null));
    }
}
