<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\ReferenceGraph;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Generator\ReferenceGraph\BrokenReferenceView;
use Sif\Builder\Generator\ReferenceGraph\ReferenceGraphEdgeView;
use Sif\Builder\Generator\ReferenceGraph\ReferenceGraphJsonRenderer;
use Sif\Builder\Generator\ReferenceGraph\ReferenceGraphNodeView;
use Sif\Builder\Generator\ReferenceGraph\ReferenceGraphView;

final class ReferenceGraphJsonRendererTest extends TestCase
{
    public function testRendersStableVersionedJsonWithExactlyOneTrailingNewline(): void
    {
        $view = new ReferenceGraphView(
            nodes: [new ReferenceGraphNodeView('ADR-001', 'Contracts', 'ADR', 'approved', '1.0.0', 1, 0)],
            edges: [new ReferenceGraphEdgeView('WP-105', 'ADR-001', 'implements', 12)],
            brokenReferences: [new BrokenReferenceView('WP-105', 'SPEC-999', 'reference', 'target_not_found', 18)],
            cycles: [],
        );

        $renderer = new ReferenceGraphJsonRenderer();
        $first = $renderer->render($view);
        $second = $renderer->render($view);

        self::assertSame($first, $second);
        self::assertStringContainsString('"schema_version": "1.0.0"', $first);
        self::assertStringContainsString('"generator": "reference.graph"', $first);
        self::assertStringEndsWith("}\n", $first);
        self::assertFalse(str_ends_with($first, "\n\n"));
    }
}
