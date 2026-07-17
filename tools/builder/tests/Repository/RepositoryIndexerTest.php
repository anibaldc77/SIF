<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Metadata\MetadataScanIssue;
use Sif\Builder\Metadata\MetadataScanResult;
use Sif\Builder\Metadata\RepositoryScannerInterface;
use Sif\Builder\Repository\RepositoryIndexBuilder;
use Sif\Builder\Repository\RepositoryIndexer;

final class RepositoryIndexerTest extends TestCase
{
    public function testIndexesDocumentsAndPreservesScanIssues(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('/repo/engineering/WP-101.md', [
            'id' => 'WP-101',
            'title' => 'Engineering Repository Index',
            'document_class' => 'WorkPackageDocument',
            'category' => 'Work Package',
            'status' => 'Draft',
            'version' => '0.1.0',
            'tags' => ['builder'],
        ]));

        $scanner = new class ($registry) implements RepositoryScannerInterface {
            public string $receivedRoot = '';

            public function __construct(private readonly MetadataRegistry $registry)
            {
            }

            public function scan(string $root): MetadataScanResult
            {
                $this->receivedRoot = $root;

                return new MetadataScanResult(
                    $this->registry,
                    [new MetadataScanIssue('/repo/engineering/invalid.md', 'Invalid metadata.')],
                );
            }
        };

        $result = (new RepositoryIndexer($scanner, new RepositoryIndexBuilder()))->index('/repo/engineering');

        self::assertSame('/repo/engineering', $scanner->receivedRoot);
        self::assertSame(1, $result->indexedCount());
        self::assertSame(1, $result->issueCount());
        self::assertFalse($result->isSuccessful());
        self::assertSame('/repo/engineering/invalid.md', $result->issues[0]->path);
        self::assertGreaterThanOrEqual(0.0, $result->durationSeconds);
    }

    public function testSuccessfulResultWhenScannerReportsNoIssues(): void
    {
        $scanner = new class implements RepositoryScannerInterface {
            public function scan(string $root): MetadataScanResult
            {
                return new MetadataScanResult(new MetadataRegistry(), []);
            }
        };

        $result = (new RepositoryIndexer($scanner, new RepositoryIndexBuilder()))->index('/repo/engineering');

        self::assertTrue($result->isSuccessful());
        self::assertSame(0, $result->indexedCount());
        self::assertSame(0, $result->issueCount());
    }
}
