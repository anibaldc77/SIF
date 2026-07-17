<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryIndexEntryTest extends TestCase
{
    public function testExposesImmutableRepositoryMetadata(): void
    {
        $entry = new RepositoryIndexEntry(
            identifier: 'EG-004',
            title: 'Engineering Repository Index',
            documentClass: 'NormativeDocument',
            category: 'Normative Specification',
            status: 'Draft for Review',
            version: '0.1.0',
            path: 'engineering/specifications/WP-101/EG-004-Engineering-Repository-Index.md',
            workPackage: 'WP-101',
            tags: [' builder ', 'repository', '', 'builder'],
        );

        self::assertSame('EG-004', $entry->identifier);
        self::assertSame('WP-101', $entry->workPackage);
        self::assertSame(['builder', 'repository'], $entry->tags);
    }

    public function testRejectsEmptyIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('identifier must not be empty');

        $this->createEntry(identifier: '  ');
    }

    public function testRejectsEmptyPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('path must not be empty');

        $this->createEntry(path: "\t");
    }

    private function createEntry(
        string $identifier = 'EG-004',
        string $path = 'engineering/specifications/WP-101/EG-004.md',
    ): RepositoryIndexEntry {
        return new RepositoryIndexEntry(
            identifier: $identifier,
            title: 'Engineering Repository Index',
            documentClass: 'NormativeDocument',
            category: 'Normative Specification',
            status: 'Draft for Review',
            version: '0.1.0',
            path: $path,
        );
    }
}
