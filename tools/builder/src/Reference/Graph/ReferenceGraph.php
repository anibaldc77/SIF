<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Graph;

use InvalidArgumentException;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolvedReference;

final readonly class ReferenceGraph
{
    /** @var array<string, list<ResolvedReference>> */
    private array $outgoing;

    /** @var array<string, list<ResolvedReference>> */
    private array $incoming;

    /** @param array<string, list<ResolvedReference>> $outgoing */
    public function __construct(array $outgoing = [])
    {
        $normalized = [];
        foreach ($outgoing as $source => $references) {
            if (trim($source) === '') {
                throw new InvalidArgumentException('Graph source identifier cannot be empty.');
            }
            foreach ($references as $reference) {
                if (!$reference instanceof ResolvedReference) {
                    throw new InvalidArgumentException('Graph edges must contain only ResolvedReference instances.');
                }
                if ($reference->reference->sourceIdentifier !== $source) {
                    throw new InvalidArgumentException('Graph edge source does not match its adjacency key.');
                }
            }
            usort($references, static fn (ResolvedReference $a, ResolvedReference $b): int => $a->reference->identity() <=> $b->reference->identity());
            $normalized[$source] = array_values($references);
        }
        ksort($normalized, SORT_STRING);
        $this->outgoing = $normalized;

        $incoming = [];
        foreach ($normalized as $references) {
            foreach ($references as $reference) {
                $incoming[$reference->reference->targetIdentifier][] = $reference;
            }
        }
        foreach ($incoming as &$references) {
            usort($references, static fn (ResolvedReference $a, ResolvedReference $b): int => $a->reference->identity() <=> $b->reference->identity());
        }
        unset($references);
        ksort($incoming, SORT_STRING);
        $this->incoming = $incoming;
    }

    public static function fromResolution(ResolutionResult $result): self
    {
        $outgoing = [];
        foreach ($result->resolved as $reference) {
            $outgoing[$reference->reference->sourceIdentifier][] = $reference;
        }

        return new self($outgoing);
    }

    /** @return list<ResolvedReference> */
    public function outgoing(string $identifier): array
    {
        return $this->outgoing[$identifier] ?? [];
    }

    /** @return list<ResolvedReference> */
    public function incoming(string $identifier): array
    {
        return $this->incoming[$identifier] ?? [];
    }

    /** @return list<string> */
    public function nodes(): array
    {
        $nodes = [];
        foreach ($this->outgoing as $source => $references) {
            $nodes[$source] = true;
            foreach ($references as $reference) {
                $nodes[$reference->reference->targetIdentifier] = true;
            }
        }
        $identifiers = array_keys($nodes);
        sort($identifiers, SORT_STRING);

        return $identifiers;
    }

    public function edgeCount(): int
    {
        return array_sum(array_map('count', $this->outgoing));
    }
}
