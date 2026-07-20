<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Graph;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Graph\ReferenceGraph;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceGraphTest extends TestCase
{
    public function testBuildsDeterministicAdjacencyFromResolution(): void
    {
        $graph = ReferenceGraph::fromResolution(new ResolutionResult([
            $this->resolved('WP-102', 'ADR-002'),
            $this->resolved('WP-102', 'ADR-001'),
            $this->resolved('ADR-001', 'WP-100'),
        ]));

        self::assertSame(['ADR-001', 'ADR-002', 'WP-100', 'WP-102'], $graph->nodes());
        self::assertSame(3, $graph->edgeCount());
        self::assertSame('ADR-001', $graph->outgoing('WP-102')[0]->reference->targetIdentifier);
        self::assertSame('WP-102', $graph->incoming('ADR-002')[0]->reference->sourceIdentifier);
        self::assertSame([], $graph->outgoing('UNKNOWN'));
    }

    private function resolved(string $source, string $target): ResolvedReference
    {
        return new ResolvedReference(new Reference($source, $target), new RepositoryIndexEntry($target, $target, 'Document', 'Engineering', 'Draft', '0.1.0', "/repo/$target.md", null));
    }
}
