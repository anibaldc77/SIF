<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceReport;

use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceReportViewFactory
{
    public function create(RepositoryWorkspace $workspace): ReferenceReportView
    {
        $index = $workspace->repositoryIndex();
        $resolution = $workspace->resolution();

        if ($index === null || $resolution === null) {
            return new ReferenceReportView(0, 0, 0, 0, [], [], [], []);
        }

        $incoming = [];
        $outgoing = [];
        $brokenOutgoing = [];
        $byType = [];
        $resolvedViews = [];
        $brokenViews = [];

        foreach ($resolution->resolved as $resolved) {
            $reference = $resolved->reference;
            $incoming[$reference->targetIdentifier] = ($incoming[$reference->targetIdentifier] ?? 0) + 1;
            $outgoing[$reference->sourceIdentifier] = ($outgoing[$reference->sourceIdentifier] ?? 0) + 1;
            $byType[$reference->type->value] = ($byType[$reference->type->value] ?? 0) + 1;
            $resolvedViews[] = $this->resolvedView($resolved);
        }

        foreach ($resolution->broken as $broken) {
            $reference = $broken->reference;
            $brokenOutgoing[$reference->sourceIdentifier] = ($brokenOutgoing[$reference->sourceIdentifier] ?? 0) + 1;
            $byType[$reference->type->value] = ($byType[$reference->type->value] ?? 0) + 1;
            $brokenViews[] = $this->brokenView($broken);
        }

        ksort($byType, SORT_STRING);
        usort($resolvedViews, static fn (ReferenceReportEntryView $a, ReferenceReportEntryView $b): int => self::compareEntry($a, $b));
        usort($brokenViews, static fn (ReferenceReportEntryView $a, ReferenceReportEntryView $b): int => self::compareEntry($a, $b));

        $documents = [];
        foreach ($index->all() as $entry) {
            $documents[] = new DocumentReferenceView(
                identifier: $entry->identifier,
                title: $this->displayTitle($entry),
                documentType: $this->documentType($entry),
                incoming: $incoming[$entry->identifier] ?? 0,
                outgoing: $outgoing[$entry->identifier] ?? 0,
                brokenOutgoing: $brokenOutgoing[$entry->identifier] ?? 0,
            );
        }

        usort($documents, static fn (DocumentReferenceView $a, DocumentReferenceView $b): int => strnatcasecmp($a->identifier, $b->identifier));

        return new ReferenceReportView(
            totalDocuments: $index->count(),
            totalReferences: $resolution->total(),
            resolvedReferences: $resolution->resolvedCount(),
            brokenReferences: $resolution->brokenCount(),
            resolved: $resolvedViews,
            broken: $brokenViews,
            documents: $documents,
            byType: $byType,
        );
    }

    private function resolvedView(ResolvedReference $resolved): ReferenceReportEntryView
    {
        $reference = $resolved->reference;

        return new ReferenceReportEntryView(
            source: $reference->sourceIdentifier,
            target: $reference->targetIdentifier,
            type: $reference->type->value,
            status: 'resolved',
            line: $reference->line,
        );
    }

    private function brokenView(BrokenReference $broken): ReferenceReportEntryView
    {
        $reference = $broken->reference;

        return new ReferenceReportEntryView(
            source: $reference->sourceIdentifier,
            target: $reference->targetIdentifier,
            type: $reference->type->value,
            status: 'broken',
            line: $reference->line,
            reason: $broken->reason,
        );
    }

    private static function compareEntry(ReferenceReportEntryView $left, ReferenceReportEntryView $right): int
    {
        return [$left->source, $left->target, $left->type, $left->line ?? 0]
            <=> [$right->source, $right->target, $right->type, $right->line ?? 0];
    }

    private function displayTitle(RepositoryIndexEntry $entry): string
    {
        $title = trim($entry->title);
        return $title === '' ? 'Unspecified' : $title;
    }

    private function documentType(RepositoryIndexEntry $entry): string
    {
        $type = strtoupper(trim($entry->documentClass));
        if ($type !== '') {
            return $type;
        }

        $parts = explode('-', strtoupper($entry->identifier), 2);
        return $parts[0] !== '' ? $parts[0] : 'UNSPECIFIED';
    }
}
