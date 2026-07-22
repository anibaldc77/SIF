<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\MetadataCompleteness;

use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final readonly class MetadataCompletenessInspector
{
    /** @return list<MetadataCompletenessFinding> */
    public function inspect(MetadataRegistry $registry, RepositoryIndex $index): array
    {
        $findings = [];

        foreach ($index->all() as $entry) {
            $document = $registry->get($entry->identifier);

            if ($document === null) {
                $findings[] = new MetadataCompletenessFinding(
                    code: 'METACOMP-201',
                    severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Indexed document "%s" has no metadata document in the workspace registry.', $entry->identifier),
                    path: $this->normalizePath($entry->path),
                    documentIdentifier: $entry->identifier,
                    field: 'id',
                    context: ['document_id' => $entry->identifier, 'field' => 'id'],
                    remediation: 'Re-run repository discovery and indexing, then correct any scanner diagnostics.',
                );
                continue;
            }

            $metadata = $document->metadata;
            $path = $this->normalizePath($entry->path);

            foreach ($this->requiredIndexValues($entry) as $field => $value) {
                if (trim($value) !== '') {
                    continue;
                }

                $findings[] = new MetadataCompletenessFinding(
                    code: 'METACOMP-202',
                    severity: DiagnosticSeverity::ERROR,
                    message: sprintf('Document "%s" has an empty required metadata value for "%s".', $entry->identifier, $field),
                    path: $path,
                    documentIdentifier: $entry->identifier,
                    field: $field,
                    context: ['document_id' => $entry->identifier, 'field' => $field],
                    remediation: sprintf('Provide a non-empty "%s" value in the document front matter.', $field),
                );
            }

            if (!array_key_exists('document_class', $metadata)) {
                $findings[] = $this->recommendedFinding($entry, $path, 'document_class');
            }

            if (!array_key_exists('summary', $metadata) || !is_string($metadata['summary']) || trim($metadata['summary']) === '') {
                $findings[] = $this->recommendedFinding($entry, $path, 'summary');
            }

            if ($entry->tags === []) {
                $findings[] = new MetadataCompletenessFinding(
                    code: 'METACOMP-204',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Document "%s" does not define any tags.', $entry->identifier),
                    path: $path,
                    documentIdentifier: $entry->identifier,
                    field: 'tags',
                    context: ['document_id' => $entry->identifier, 'field' => 'tags'],
                    remediation: 'Add at least one stable tag that supports repository discovery and navigation.',
                );
            }

            if ($entry->category === 'Work Package' && ($entry->workPackage === null || trim($entry->workPackage) === '')) {
                $findings[] = new MetadataCompletenessFinding(
                    code: 'METACOMP-205',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Work package document "%s" does not declare a work package identifier.', $entry->identifier),
                    path: $path,
                    documentIdentifier: $entry->identifier,
                    field: 'work_package',
                    context: ['document_id' => $entry->identifier, 'field' => 'work_package'],
                    remediation: 'Declare the owning work package in the document metadata.',
                );
            }
        }

        foreach ($registry->all() as $document) {
            $identifier = $document->id();
            if ($identifier === '' || $index->has($identifier)) {
                continue;
            }

            $findings[] = new MetadataCompletenessFinding(
                code: 'METACOMP-206',
                severity: DiagnosticSeverity::ERROR,
                message: sprintf('Metadata document "%s" is missing from the repository index.', $identifier),
                path: $this->normalizePath($document->path),
                documentIdentifier: $identifier,
                field: 'id',
                context: ['document_id' => $identifier, 'field' => 'id'],
                remediation: 'Correct indexing inputs and rebuild the repository index.',
            );
        }

        usort($findings, static fn (MetadataCompletenessFinding $left, MetadataCompletenessFinding $right): int => $left->identity() <=> $right->identity());

        return array_values($findings);
    }

    /** @return array<string, string> */
    private function requiredIndexValues(RepositoryIndexEntry $entry): array
    {
        return [
            'title' => $entry->title,
            'document_class' => $entry->documentClass,
            'category' => $entry->category,
            'status' => $entry->status,
            'version' => $entry->version,
        ];
    }

    private function recommendedFinding(RepositoryIndexEntry $entry, string $path, string $field): MetadataCompletenessFinding
    {
        return new MetadataCompletenessFinding(
            code: 'METACOMP-203',
            severity: DiagnosticSeverity::WARNING,
            message: sprintf('Document "%s" does not define the recommended metadata field "%s".', $entry->identifier, $field),
            path: $path,
            documentIdentifier: $entry->identifier,
            field: $field,
            context: ['document_id' => $entry->identifier, 'field' => $field],
            remediation: sprintf('Add the recommended "%s" field to the document front matter.', $field),
        );
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
