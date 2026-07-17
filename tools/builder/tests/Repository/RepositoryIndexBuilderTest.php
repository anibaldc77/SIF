<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;
use Sif\Builder\Repository\RepositoryIndexBuilder;

final class RepositoryIndexBuilderTest extends TestCase
{
    public function testBuildsIndexFromMetadataRegistry(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('/repo/engineering/standards/ES-004.md', [
            'id' => 'ES-004',
            'title' => 'Repository Index',
            'document_class' => 'NormativeDocument',
            'category' => 'Engineering Standard',
            'status' => 'Draft',
            'version' => '0.1.0',
            'work_package' => 'WP-101',
            'tags' => ['repository', 'metadata'],
        ]));

        $index = (new RepositoryIndexBuilder())->build($registry);
        $entry = $index->get('ES-004');

        self::assertSame(1, $index->count());
        self::assertNotNull($entry);
        self::assertSame('Repository Index', $entry->title);
        self::assertSame('WP-101', $entry->workPackage);
        self::assertSame(['repository', 'metadata'], $entry->tags);
        self::assertSame('/repo/engineering/standards/ES-004.md', $entry->path);
    }

    public function testBuildsEmptyIndexFromEmptyRegistry(): void
    {
        $index = (new RepositoryIndexBuilder())->build(new MetadataRegistry());

        self::assertTrue($index->isEmpty());
    }
}
