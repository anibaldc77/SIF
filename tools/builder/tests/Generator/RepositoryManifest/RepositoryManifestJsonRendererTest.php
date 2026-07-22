<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Generator\RepositoryManifest;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestDocumentView;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestJsonRenderer;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestReferenceView;
use Sif\Builder\Generator\RepositoryManifest\RepositoryManifestView;

final class RepositoryManifestJsonRendererTest extends TestCase
{
    public function testRendersStableVersionedJsonAndDeclaresFingerprintScope(): void
    {
        $view = new RepositoryManifestView(
            documents: [new RepositoryManifestDocumentView('ADR-001', 'Contracts', 'ADR', 'architecture', 'approved', '1.0.0', 'engineering/ADR-001.md', null, [], 1, 0, 0, str_repeat('a', 64))],
            references: [new RepositoryManifestReferenceView('WP-105', 'ADR-001', 'implements', 12, true)],
            documentsByType: ['ADR' => 1],
            documentsByStatus: ['approved' => 1],
        );

        $renderer = new RepositoryManifestJsonRenderer();
        $first = $renderer->render($view);
        $second = $renderer->render($view);

        self::assertSame($first, $second);
        self::assertStringContainsString('"schema_version": "1.0.0"', $first);
        self::assertStringContainsString('"content_hashes_available": false', $first);
        self::assertStringContainsString('"scope": "normalized_index_metadata"', $first);
        self::assertStringEndsWith("}\n", $first);
        self::assertFalse(str_ends_with($first, "\n\n"));
    }
}
