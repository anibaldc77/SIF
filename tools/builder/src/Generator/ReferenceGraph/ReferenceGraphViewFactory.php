<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceGraph;

use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Reference\Graph\ReferenceCycleDetector;
use Sif\Builder\Reference\Graph\ReferenceGraph;
use Sif\Builder\Repository\RepositoryIndexEntry;

final readonly class ReferenceGraphViewFactory
{
    public function __construct(
        private ReferenceCycleDetector $cycleDetector = new ReferenceCycleDetector(),
    ) {
    }

    public function create(RepositoryWorkspace $workspace): ReferenceGraphView
    {
        $index = $workspace->repositoryIndex();
        $resolution = $workspace->resolution();

        if ($index === null || $resolution === null) {
            return new ReferenceGraphView([], [], [], []);
        }

        $graph = ReferenceGraph::fromResolution($resolution);
        $incoming = [];
        $outgoing = [];
        $edges = [];

        foreach ($resolution->resolved as $resolved) {
            $reference = $resolved->reference;
            $incoming[$reference->targetIdentifier] = ($incoming[$reference->targetIdentifier] ?? 0) + 1;
            $outgoing[$reference->sourceIdentifier] = ($outgoing[$reference->sourceIdentifier] ?? 0) + 1;
            $edges[] = new ReferenceGraphEdgeView(
                source: $reference->sourceIdentifier,
                target: $reference->targetIdentifier,
                type: $reference->type->value,
                line: $reference->line,
            );
        }

        usort($edges, static fn (ReferenceGraphEdgeView $left, ReferenceGraphEdgeView $right): int => [
            $left->source,
            $left->target,
            $left->type,
            $left->line ?? 0,
        ] <=> [
            $right->source,
            $right->target,
            $right->type,
            $right->line ?? 0,
        ]);

        $broken = [];
        foreach ($resolution->broken as $item) {
            $reference = $item->reference;
            $broken[] = new BrokenReferenceView(
                source: $reference->sourceIdentifier,
                target: $reference->targetIdentifier,
                type: $reference->type->value,
                reason: $item->reason,
                line: $reference->line,
            );
        }

        usort($broken, static fn (BrokenReferenceView $left, BrokenReferenceView $right): int => [
            $left->source,
            $left->target,
            $left->type,
            $left->line ?? 0,
            $left->reason,
        ] <=> [
            $right->source,
            $right->target,
            $right->type,
            $right->line ?? 0,
            $right->reason,
        ]);

        $nodes = [];
        foreach ($index->all() as $entry) {
            $nodes[] = $this->node($entry, $incoming[$entry->identifier] ?? 0, $outgoing[$entry->identifier] ?? 0);
        }
        usort($nodes, static fn (ReferenceGraphNodeView $left, ReferenceGraphNodeView $right): int => strnatcasecmp($left->identifier, $right->identifier));

        $cycles = array_map(
            static fn ($cycle): array => $cycle->path,
            $this->cycleDetector->detect($graph),
        );

        return new ReferenceGraphView($nodes, $edges, $broken, $cycles);
    }

    private function node(RepositoryIndexEntry $entry, int $incoming, int $outgoing): ReferenceGraphNodeView
    {
        $type = strtoupper(trim($entry->documentClass));
        if ($type === '') {
            $parts = explode('-', strtoupper($entry->identifier), 2);
            $type = $parts[0] !== '' ? $parts[0] : 'UNSPECIFIED';
        }

        return new ReferenceGraphNodeView(
            identifier: $entry->identifier,
            title: trim($entry->title) === '' ? 'Unspecified' : trim($entry->title),
            documentType: $type,
            status: trim($entry->status) === '' ? 'unspecified' : trim($entry->status),
            version: trim($entry->version) === '' ? 'unspecified' : trim($entry->version),
            incoming: $incoming,
            outgoing: $outgoing,
        );
    }
}
