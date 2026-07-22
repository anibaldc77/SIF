<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\DocumentConsistency;

use DateTimeImmutable;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Metadata\DocumentClass;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final readonly class DocumentConsistencyInspector
{
    /** @var list<string> */
    private const STATUSES = [
        'Draft',
        'Draft for Review',
        'Technical Review',
        'Release Candidate',
        'Approved',
        'Deprecated',
        'Superseded',
        'Archived',
    ];

    /** @var array<string, DocumentClass> */
    private const CATEGORY_CLASSES = [
        'Constitution' => DocumentClass::Normative,
        'Architecture Specification' => DocumentClass::Normative,
        'Engineering Standard' => DocumentClass::Normative,
        'Policy' => DocumentClass::Governance,
        'Architecture Decision Record' => DocumentClass::Governance,
        'Request for Comments' => DocumentClass::Governance,
        'Work Package' => DocumentClass::Governance,
        'Normative Specification' => DocumentClass::Normative,
        'Architecture Review' => DocumentClass::Review,
        'Implementation Review' => DocumentClass::Review,
        'Informative Document' => DocumentClass::Informative,
        'Template' => DocumentClass::Template,
    ];

    /** @return list<DocumentConsistencyFinding> */
    public function inspect(MetadataRegistry $registry): array
    {
        $findings = [];

        foreach ($registry->all() as $document) {
            $metadata = $document->metadata;
            $identifier = $document->id();

            $status = $metadata['status'] ?? null;
            if (!is_string($status) || !in_array($status, self::STATUSES, true)) {
                $findings[] = $this->finding(
                    'DOCCONS-201',
                    DiagnosticSeverity::ERROR,
                    $document,
                    sprintf('Document "%s" declares an unregistered status.', $identifier),
                    ['status' => is_scalar($status) ? (string) $status : get_debug_type($status)],
                    'Use a status registered by the metadata governance specification.',
                );
            }

            $version = $metadata['version'] ?? null;
            if (!is_string($version) || preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?$/', $version) !== 1) {
                $findings[] = $this->finding(
                    'DOCCONS-202',
                    DiagnosticSeverity::ERROR,
                    $document,
                    sprintf('Document "%s" declares a version that does not conform to Semantic Versioning.', $identifier),
                    ['version' => is_scalar($version) ? (string) $version : get_debug_type($version)],
                    'Declare a valid Semantic Version such as 1.0.0 or 2.0.0-rc.1.',
                );
            }

            $category = $metadata['category'] ?? null;
            $documentClass = $metadata['document_class'] ?? null;
            if (is_string($category) && isset(self::CATEGORY_CLASSES[$category])) {
                $expected = self::CATEGORY_CLASSES[$category]->value;
                if (!is_string($documentClass) || $documentClass !== $expected) {
                    $findings[] = $this->finding(
                        'DOCCONS-203',
                        DiagnosticSeverity::ERROR,
                        $document,
                        sprintf('Document "%s" uses document class "%s" for category "%s"; expected "%s".', $identifier, is_scalar($documentClass) ? (string) $documentClass : get_debug_type($documentClass), $category, $expected),
                        [
                            'category' => $category,
                            'document_class' => is_scalar($documentClass) ? (string) $documentClass : get_debug_type($documentClass),
                            'expected_document_class' => $expected,
                        ],
                        'Align document_class with the class assigned to the declared category.',
                    );
                }
            }

            if ($status === 'Superseded') {
                $supersededBy = $metadata['superseded_by'] ?? null;
                if (!is_string($supersededBy) || trim($supersededBy) === '') {
                    $findings[] = $this->finding(
                        'DOCCONS-204',
                        DiagnosticSeverity::ERROR,
                        $document,
                        sprintf('Superseded document "%s" does not identify its replacement.', $identifier),
                        ['status' => 'Superseded'],
                        'Declare a non-empty superseded_by identifier.',
                    );
                }
            } elseif (isset($metadata['superseded_by']) && is_string($metadata['superseded_by']) && trim($metadata['superseded_by']) !== '') {
                $findings[] = $this->finding(
                    'DOCCONS-204',
                    DiagnosticSeverity::WARNING,
                    $document,
                    sprintf('Document "%s" declares superseded_by while its status is "%s".', $identifier, is_string($status) ? $status : get_debug_type($status)),
                    [
                        'status' => is_scalar($status) ? (string) $status : get_debug_type($status),
                        'superseded_by' => $metadata['superseded_by'],
                    ],
                    'Set status to Superseded or remove superseded_by.',
                );
            }

            $created = $this->date($metadata['created'] ?? null);
            $updated = $this->date($metadata['updated'] ?? null);
            if ($created === null || $updated === null) {
                $findings[] = $this->finding(
                    'DOCCONS-205',
                    DiagnosticSeverity::ERROR,
                    $document,
                    sprintf('Document "%s" contains an invalid created or updated date.', $identifier),
                    [
                        'created' => is_scalar($metadata['created'] ?? null) ? (string) $metadata['created'] : get_debug_type($metadata['created'] ?? null),
                        'updated' => is_scalar($metadata['updated'] ?? null) ? (string) $metadata['updated'] : get_debug_type($metadata['updated'] ?? null),
                    ],
                    'Use calendar dates in YYYY-MM-DD format.',
                );
            } elseif ($updated < $created) {
                $findings[] = $this->finding(
                    'DOCCONS-205',
                    DiagnosticSeverity::ERROR,
                    $document,
                    sprintf('Document "%s" was updated before it was created.', $identifier),
                    [
                        'created' => $created->format('Y-m-d'),
                        'updated' => $updated->format('Y-m-d'),
                    ],
                    'Set updated to the creation date or a later date.',
                );
            }

            $baseName = pathinfo(str_replace('\\', '/', $document->path), PATHINFO_FILENAME);
            if ($identifier !== '' && $baseName !== $identifier && !str_starts_with($baseName, $identifier . '-')) {
                $findings[] = $this->finding(
                    'DOCCONS-206',
                    DiagnosticSeverity::WARNING,
                    $document,
                    sprintf('Document identifier "%s" is inconsistent with filename "%s".', $identifier, $baseName),
                    ['identifier' => $identifier, 'filename' => $baseName],
                    'Rename the file so its basename starts with the document identifier.',
                );
            }
        }

        usort(
            $findings,
            static fn (DocumentConsistencyFinding $left, DocumentConsistencyFinding $right): int => $left->identity() <=> $right->identity(),
        );

        return $findings;
    }

    /** @param array<string, scalar|null> $context */
    private function finding(
        string $code,
        DiagnosticSeverity $severity,
        MetadataDocument $document,
        string $message,
        array $context,
        string $remediation,
    ): DocumentConsistencyFinding {
        return new DocumentConsistencyFinding(
            code: $code,
            severity: $severity,
            message: $message,
            sourceIdentifier: $document->id(),
            sourcePath: $document->path,
            context: $context,
            remediation: $remediation,
        );
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $date->format('Y-m-d') === $value ? $date : null;
    }
}
