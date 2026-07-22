<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\ReferenceGraph;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Generator\ReferenceGraph\ReferenceGraphViewFactory;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceGraphViewFactoryTest extends TestCase
{
    public function testBuildsDeterministicGraphIncludingCyclesAndIsolatedNodes(): void
    {
        $index = new RepositoryIndex();
        $a = $this->entry('ADR-001');
        $b = $this->entry('WP-105');
        $c = $this->entry('RFC-002');
        $index->add($c);
        $index->add($b);
        $index->add($a);

        $resolution = new ResolutionResult(
            resolved: [
                new ResolvedReference(new Reference('WP-105', 'ADR-001', ReferenceType::IMPLEMENTS), $a),
                new ResolvedReference(new Reference('ADR-001', 'WP-105', ReferenceType::RELATED), $b),
            ],
            broken: [new BrokenReference(new Reference('WP-105', 'SPEC-999', ReferenceType::REFERENCE), 'target_not_found')],
        );

        $view = (new ReferenceGraphViewFactory())->create(new RepositoryWorkspace(repositoryIndex: $index, resolution: $resolution));

        self::assertSame(['ADR-001', 'RFC-002', 'WP-105'], array_map(static fn ($node): string => $node->identifier, $view->nodes));
        self::assertSame(2, $view->edgeCount());
        self::assertSame('ADR-001', $view->edges[0]->source);
        self::assertSame([['ADR-001', 'WP-105', 'ADR-001']], $view->cycles);
        self::assertSame('SPEC-999', $view->brokenReferences[0]->target);
    }

    private function entry(string $identifier): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry($identifier, $identifier, explode('-', $identifier, 2)[0], 'engineering', 'approved', '1.0.0', 'engineering/' . $identifier . '.md');
    }
}
