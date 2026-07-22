<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceGraph;

final readonly class ReferenceGraphView
{
    /**
     * @param list<ReferenceGraphNodeView> $nodes
     * @param list<ReferenceGraphEdgeView> $edges
     * @param list<BrokenReferenceView> $brokenReferences
     * @param list<list<string>> $cycles
     */
    public function __construct(
        public array $nodes,
        public array $edges,
        public array $brokenReferences,
        public array $cycles,
    ) {
    }

    public function nodeCount(): int
    {
        return count($this->nodes);
    }

    public function edgeCount(): int
    {
        return count($this->edges);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'summary' => [
                'nodes' => $this->nodeCount(),
                'edges' => $this->edgeCount(),
                'broken_references' => count($this->brokenReferences),
                'cycles' => count($this->cycles),
            ],
            'nodes' => array_map(
                static fn (ReferenceGraphNodeView $node): array => $node->toArray(),
                $this->nodes,
            ),
            'edges' => array_map(
                static fn (ReferenceGraphEdgeView $edge): array => $edge->toArray(),
                $this->edges,
            ),
            'broken_references' => array_map(
                static fn (BrokenReferenceView $reference): array => $reference->toArray(),
                $this->brokenReferences,
            ),
            'cycles' => $this->cycles,
        ];
    }
}
