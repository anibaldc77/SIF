<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryManifest;

use JsonException;
use RuntimeException;

final readonly class RepositoryManifestJsonRenderer
{
    public const SCHEMA_VERSION = '1.0.0';

    public function render(RepositoryManifestView $view): string
    {
        try {
            $json = json_encode([
                'schema_version' => self::SCHEMA_VERSION,
                'generated_by' => 'sif-builder',
                'generator' => RepositoryManifestGenerator::IDENTIFIER,
                'integrity' => [
                    'content_hashes_available' => false,
                    'entry_fingerprint_scope' => 'normalized_index_metadata',
                ],
                'summary' => [
                    'documents' => count($view->documents),
                    'references' => count($view->references),
                    'resolved_references' => $view->resolvedReferenceCount(),
                    'broken_references' => $view->brokenReferenceCount(),
                    'documents_by_type' => $view->documentsByType,
                    'documents_by_status' => $view->documentsByStatus,
                ],
                'documents' => array_map(
                    static fn (RepositoryManifestDocumentView $document): array => $document->jsonSerialize(),
                    $view->documents,
                ),
                'references' => array_map(
                    static fn (RepositoryManifestReferenceView $reference): array => $reference->jsonSerialize(),
                    $view->references,
                ),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to render repository manifest JSON.', 0, $exception);
        }

        return $json . "\n";
    }
}
