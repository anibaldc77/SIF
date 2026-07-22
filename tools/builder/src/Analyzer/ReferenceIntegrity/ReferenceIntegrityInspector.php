<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\ReferenceIntegrity;

use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Reference\Graph\ReferenceCycleDetector;
use Sif\Builder\Reference\Graph\ReferenceGraph;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Repository\RepositoryIndex;

final readonly class ReferenceIntegrityInspector
{
    public function __construct(private ReferenceCycleDetector $cycleDetector = new ReferenceCycleDetector())
    {
    }

    /** @return list<ReferenceIntegrityFinding> */
    public function inspect(RepositoryIndex $index, ResolutionResult $resolution): array
    {
        $findings = [];

        foreach ($resolution->broken as $broken) {
            $reference = $broken->reference;
            $findings[] = new ReferenceIntegrityFinding(
                code: 'REFINT-201',
                severity: DiagnosticSeverity::ERROR,
                message: sprintf(
                    'Document "%s" references missing target "%s".',
                    $reference->sourceIdentifier,
                    $reference->targetIdentifier,
                ),
                sourceIdentifier: $reference->sourceIdentifier,
                sourcePath: $this->pathFor($index, $reference->sourceIdentifier),
                context: [
                    'source_id' => $reference->sourceIdentifier,
                    'target_id' => $reference->targetIdentifier,
                    'reference_type' => $reference->type->value,
                    'reason' => $broken->reason,
                    'line' => $reference->line,
                    'column' => $reference->column,
                ],
                remediation: 'Create the referenced document or correct the target identifier.',
            );
        }

        $graph = ReferenceGraph::fromResolution($resolution);
        foreach ($this->cycleDetector->detect($graph) as $cycle) {
            $findings[] = new ReferenceIntegrityFinding(
                code: 'REFINT-202',
                severity: DiagnosticSeverity::WARNING,
                message: sprintf('Reference cycle detected: %s.', implode(' -> ', $cycle->path)),
                sourceIdentifier: $cycle->path[0],
                sourcePath: $this->pathFor($index, $cycle->path[0]),
                context: ['cycle' => implode(' -> ', $cycle->path)],
                remediation: 'Review the dependency direction and remove the cycle when it violates repository governance.',
            );
        }

        /** @var array<string, array{source: string, target: string, type: string, count: int, path: ?string}> $relationships */
        $relationships = [];
        foreach ($resolution->resolved as $resolved) {
            $reference = $resolved->reference;

            if ($reference->sourceIdentifier === $reference->targetIdentifier) {
                $findings[] = new ReferenceIntegrityFinding(
                    code: 'REFINT-203',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Document "%s" contains a self-reference.', $reference->sourceIdentifier),
                    sourceIdentifier: $reference->sourceIdentifier,
                    sourcePath: $this->pathFor($index, $reference->sourceIdentifier),
                    context: [
                        'document_id' => $reference->sourceIdentifier,
                        'reference_type' => $reference->type->value,
                        'line' => $reference->line,
                        'column' => $reference->column,
                    ],
                    remediation: 'Remove the self-reference unless it is explicitly required and documented.',
                );
            }

            $key = implode('|', [$reference->sourceIdentifier, $reference->targetIdentifier, $reference->type->value]);
            if (!isset($relationships[$key])) {
                $relationships[$key] = [
                    'source' => $reference->sourceIdentifier,
                    'target' => $reference->targetIdentifier,
                    'type' => $reference->type->value,
                    'count' => 0,
                    'path' => $this->pathFor($index, $reference->sourceIdentifier),
                ];
            }
            ++$relationships[$key]['count'];
        }

        foreach ($relationships as $relationship) {
            if ($relationship['count'] < 2) {
                continue;
            }

            $findings[] = new ReferenceIntegrityFinding(
                code: 'REFINT-204',
                severity: DiagnosticSeverity::WARNING,
                message: sprintf(
                    'Document "%s" declares the relationship "%s" to "%s" %d times.',
                    $relationship['source'],
                    $relationship['type'],
                    $relationship['target'],
                    $relationship['count'],
                ),
                sourceIdentifier: $relationship['source'],
                sourcePath: $relationship['path'],
                context: [
                    'source_id' => $relationship['source'],
                    'target_id' => $relationship['target'],
                    'reference_type' => $relationship['type'],
                    'occurrences' => $relationship['count'],
                ],
                remediation: 'Keep a single governed relationship unless repeated occurrences are semantically necessary.',
            );
        }

        usort(
            $findings,
            static fn (ReferenceIntegrityFinding $left, ReferenceIntegrityFinding $right): int =>
                $left->identity() <=> $right->identity(),
        );

        return array_values($findings);
    }

    private function pathFor(RepositoryIndex $index, string $identifier): ?string
    {
        $entry = $index->get($identifier);

        return $entry === null ? null : str_replace('\\', '/', $entry->path);
    }
}
