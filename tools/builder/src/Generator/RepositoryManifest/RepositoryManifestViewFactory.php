<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryManifest;

use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Repository\RepositoryIndexEntry;

final readonly class RepositoryManifestViewFactory
{
    public function create(RepositoryWorkspace $workspace): RepositoryManifestView
    {
        $index = $workspace->repositoryIndex();
        $resolution = $workspace->resolution();

        if ($index === null || $resolution === null) {
            return new RepositoryManifestView([], [], [], []);
        }

        $incoming = [];
        $outgoing = [];
        $broken = [];
        $references = [];

        foreach ($resolution->resolved as $resolved) {
            $reference = $resolved->reference;
            $incoming[$reference->targetIdentifier] = ($incoming[$reference->targetIdentifier] ?? 0) + 1;
            $outgoing[$reference->sourceIdentifier] = ($outgoing[$reference->sourceIdentifier] ?? 0) + 1;
            $references[] = new RepositoryManifestReferenceView(
                $reference->sourceIdentifier,
                $reference->targetIdentifier,
                $reference->type->value,
                $reference->line,
                true,
            );
        }

        foreach ($resolution->broken as $item) {
            $reference = $item->reference;
            $broken[$reference->sourceIdentifier] = ($broken[$reference->sourceIdentifier] ?? 0) + 1;
            $references[] = new RepositoryManifestReferenceView(
                $reference->sourceIdentifier,
                $reference->targetIdentifier,
                $reference->type->value,
                $reference->line,
                false,
                $item->reason,
            );
        }

        usort($references, static fn (RepositoryManifestReferenceView $left, RepositoryManifestReferenceView $right): int => [
            $left->source,
            $left->target,
            $left->type,
            $left->line ?? 0,
            $left->resolved ? 0 : 1,
            $left->reason ?? '',
        ] <=> [
            $right->source,
            $right->target,
            $right->type,
            $right->line ?? 0,
            $right->resolved ? 0 : 1,
            $right->reason ?? '',
        ]);

        $documents = [];
        $byType = [];
        $byStatus = [];

        foreach ($index->all() as $entry) {
            $document = $this->document(
                $entry,
                $incoming[$entry->identifier] ?? 0,
                $outgoing[$entry->identifier] ?? 0,
                $broken[$entry->identifier] ?? 0,
            );
            $documents[] = $document;
            $byType[$document->documentType] = ($byType[$document->documentType] ?? 0) + 1;
            $byStatus[$document->status] = ($byStatus[$document->status] ?? 0) + 1;
        }

        usort($documents, static fn (RepositoryManifestDocumentView $left, RepositoryManifestDocumentView $right): int => strnatcasecmp($left->identifier, $right->identifier));
        ksort($byType, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($byStatus, SORT_NATURAL | SORT_FLAG_CASE);

        return new RepositoryManifestView($documents, $references, $byType, $byStatus);
    }

    private function document(RepositoryIndexEntry $entry, int $incoming, int $outgoing, int $broken): RepositoryManifestDocumentView
    {
        $type = strtoupper(trim($entry->documentClass));
        if ($type === '') {
            $type = strtoupper(explode('-', $entry->identifier, 2)[0] ?? 'UNSPECIFIED');
        }

        $tags = $entry->tags;
        sort($tags, SORT_NATURAL | SORT_FLAG_CASE);
        $path = str_replace('\\', '/', $entry->path);

        $normalized = [
            'identifier' => $entry->identifier,
            'title' => trim($entry->title),
            'document_type' => $type,
            'category' => trim($entry->category),
            'status' => trim($entry->status) === '' ? 'unspecified' : trim($entry->status),
            'version' => trim($entry->version) === '' ? 'unspecified' : trim($entry->version),
            'path' => $path,
            'work_package' => $entry->workPackage,
            'tags' => $tags,
        ];

        $encoded = json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new RepositoryManifestDocumentView(
            identifier: $entry->identifier,
            title: trim($entry->title) === '' ? 'Unspecified' : trim($entry->title),
            documentType: $type,
            category: trim($entry->category) === '' ? 'unspecified' : trim($entry->category),
            status: trim($entry->status) === '' ? 'unspecified' : trim($entry->status),
            version: trim($entry->version) === '' ? 'unspecified' : trim($entry->version),
            path: $path,
            workPackage: $entry->workPackage,
            tags: $tags,
            incomingReferences: $incoming,
            outgoingReferences: $outgoing,
            brokenReferences: $broken,
            entryFingerprint: hash('sha256', $encoded),
        );
    }
}
