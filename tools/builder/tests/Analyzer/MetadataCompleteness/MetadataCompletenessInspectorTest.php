<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\MetadataCompleteness;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\MetadataCompleteness\MetadataCompletenessInspector;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class MetadataCompletenessInspectorTest extends TestCase
{
    public function testReportsRecommendedMetadataAndEmptyTagsDeterministically(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/WP-106.md', [
            'id' => 'WP-106',
            'title' => 'Analyzers',
        ]));

        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry(
            identifier: 'WP-106',
            title: 'Analyzers',
            documentClass: 'Governance',
            category: 'Work Package',
            status: 'Approved',
            version: '1.0.0',
            path: 'engineering\\WP-106.md',
        ));

        $findings = (new MetadataCompletenessInspector())->inspect($registry, $index);

        self::assertSame(['METACOMP-203', 'METACOMP-203', 'METACOMP-204', 'METACOMP-205'], array_map(
            static fn ($finding): string => $finding->code,
            $findings,
        ));
        self::assertSame('engineering/WP-106.md', $findings[0]->path);
    }

    public function testReportsRegistryAndIndexParityFailures(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('engineering/ADR-001.md', ['id' => 'ADR-001']));

        $index = new RepositoryIndex();
        $index->add(new RepositoryIndexEntry('ADR-002', '', '', '', '', '', 'engineering/ADR-002.md'));

        $codes = array_map(
            static fn ($finding): string => $finding->code,
            (new MetadataCompletenessInspector())->inspect($registry, $index),
        );

        self::assertContains('METACOMP-201', $codes);
        self::assertContains('METACOMP-206', $codes);
    }
}
