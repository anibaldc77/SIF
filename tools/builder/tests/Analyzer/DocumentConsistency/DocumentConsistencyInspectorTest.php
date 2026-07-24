<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\DocumentConsistency;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\DocumentConsistency\DocumentConsistencyInspector;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final class DocumentConsistencyInspectorTest extends TestCase
{
    public function testAcceptsConsistentDocument(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001-Decision.md', [
            'id' => 'ADR-001',
            'title' => 'Decision',
            'status' => 'Approved',
            'version' => '1.0.0',
            'category' => 'Architecture Decision Record',
            'document_class' => 'GovernanceDocument',
            'created' => '2026-07-01',
            'updated' => '2026-07-22',
        ]));

        self::assertSame([], (new DocumentConsistencyInspector())->inspect($registry));
    }


    public function testAcceptsCanonicalFilenameVariantsAndScopedIdentifiers(): void
    {
        $registry = new MetadataRegistry();
        $documents = [
            ['README.md', 'SIF-README'],
            ['CODE_OF_CONDUCT.md', 'CODE-OF-CONDUCT'],
            ['WP-111-Implementation-Report.md', 'WP-111-IMPLEMENTATION-REPORT'],
            ['01-Foundation.md', 'WP-004-01-FOUNDATION'],
            ['repository-discovery.md', 'REPOSITORY-DISCOVERY'],
            ['INCREMENT-5-DOCUMENT-CLASSIFICATION-NORMALIZATION.md', 'WP-108-I5'],
        ];

        foreach ($documents as [$path, $identifier]) {
            $registry->register(new MetadataDocument($path, [
                'id' => $identifier,
                'status' => 'Approved',
                'version' => '1.0.0',
                'category' => 'Informative Document',
                'document_class' => 'InformativeDocument',
                'created' => '2026-07-01',
                'updated' => '2026-07-22',
            ]));
        }

        self::assertSame([], (new DocumentConsistencyInspector())->inspect($registry));
    }

    public function testReportsAllSupportedConsistencyFailuresDeterministically(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/EG-999-Unrelated.md', [
            'id' => 'WP-106',
            'title' => 'Work Package',
            'status' => 'released',
            'version' => 'v1.0',
            'category' => 'Work Package',
            'document_class' => 'NormativeDocument',
            'created' => '2026-07-22',
            'updated' => '2026-02-31',
            'superseded_by' => 'WP-107',
        ]));

        $inspector = new DocumentConsistencyInspector();
        $findings = $inspector->inspect($registry);
        $codes = array_map(static fn ($finding): string => $finding->code, $findings);

        self::assertSame([
            'DOCCONS-201',
            'DOCCONS-202',
            'DOCCONS-203',
            'DOCCONS-204',
            'DOCCONS-205',
            'DOCCONS-206',
        ], $codes);
        self::assertEquals($findings, $inspector->inspect($registry));
    }

    public function testReportsSupersededDocumentWithoutReplacementAndChronologicalFailure(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/RFC-001.md', [
            'id' => 'RFC-001',
            'status' => 'Superseded',
            'version' => '1.0.0',
            'category' => 'Request for Comments',
            'document_class' => 'GovernanceDocument',
            'created' => '2026-07-22',
            'updated' => '2026-07-21',
        ]));

        $codes = array_map(
            static fn ($finding): string => $finding->code,
            (new DocumentConsistencyInspector())->inspect($registry),
        );

        self::assertSame(['DOCCONS-204', 'DOCCONS-205'], $codes);
    }
}
