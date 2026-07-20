<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Graph;

final class ReferenceImpactAnalyzer
{
    /** @return list<string> */
    public function directDependents(ReferenceGraph $graph, string $identifier): array
    {
        $items = [];
        foreach ($graph->incoming($identifier) as $edge) {
            $items[$edge->reference->sourceIdentifier] = true;
        }
        $identifiers = array_keys($items);
        sort($identifiers, SORT_STRING);

        return $identifiers;
    }

    /** @return list<string> */
    public function transitiveDependents(ReferenceGraph $graph, string $identifier): array
    {
        $visited = [];
        $queue = $this->directDependents($graph, $identifier);
        while ($queue !== []) {
            $current = array_shift($queue);
            if ($current === $identifier || isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;
            foreach ($this->directDependents($graph, $current) as $dependent) {
                if (!isset($visited[$dependent])) {
                    $queue[] = $dependent;
                }
            }
        }
        $identifiers = array_keys($visited);
        sort($identifiers, SORT_STRING);

        return $identifiers;
    }
}
