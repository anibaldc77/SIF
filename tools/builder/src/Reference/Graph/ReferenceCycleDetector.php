<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Graph;

final class ReferenceCycleDetector
{
    /** @return list<ReferenceCycle> */
    public function detect(ReferenceGraph $graph): array
    {
        $cycles = [];
        foreach ($graph->nodes() as $start) {
            $this->walk($graph, $start, $start, [$start], [], $cycles);
        }
        ksort($cycles, SORT_STRING);

        return array_values($cycles);
    }

    /** @param list<string> $path @param array<string, true> $visited @param array<string, ReferenceCycle> $cycles */
    private function walk(ReferenceGraph $graph, string $start, string $current, array $path, array $visited, array &$cycles): void
    {
        $visited[$current] = true;
        foreach ($graph->outgoing($current) as $edge) {
            $next = $edge->reference->targetIdentifier;
            if ($next === $start) {
                $cyclePath = [...$path, $start];
                $canonical = $this->canonicalize($cyclePath);
                $cycle = new ReferenceCycle($canonical);
                $cycles[$cycle->identity()] = $cycle;
                continue;
            }
            if (isset($visited[$next])) {
                continue;
            }
            $this->walk($graph, $start, $next, [...$path, $next], $visited, $cycles);
        }
    }

    /** @param list<string> $closedPath @return list<string> */
    private function canonicalize(array $closedPath): array
    {
        array_pop($closedPath);
        $rotations = [];
        $count = count($closedPath);
        for ($i = 0; $i < $count; ++$i) {
            $rotation = [...array_slice($closedPath, $i), ...array_slice($closedPath, 0, $i)];
            $rotations[implode('->', $rotation)] = $rotation;
        }
        ksort($rotations, SORT_STRING);
        $canonical = reset($rotations);

        return [...$canonical, $canonical[0]];
    }
}
