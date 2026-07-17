<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Repository\Exception\DuplicateRepositoryEntryException;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryIndexTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $index = new RepositoryIndex();

        self::assertTrue($index->isEmpty());
        self::assertSame(0, $index->count());
        self::assertSame([], $index->all());
    }

    public function testRegistersAndRetrievesEntries(): void
    {
        $index = new RepositoryIndex();
        $entry = $this->entry('EG-004', '/repo/EG-004.md');

        $index->add($entry);

        self::assertFalse($index->isEmpty());
        self::assertTrue($index->has('EG-004'));
        self::assertSame($entry, $index->get('EG-004'));
        self::assertNull($index->get('UNKNOWN'));
        self::assertSame(1, $index->count());
    }

    public function testEnumeratesEntriesByIdentifier(): void
    {
        $index = new RepositoryIndex();
        $index->add($this->entry('WP-101', '/repo/WP-101.md'));
        $index->add($this->entry('ADR-0004', '/repo/ADR-0004.md'));
        $index->add($this->entry('EG-004', '/repo/EG-004.md'));

        self::assertSame(
            ['ADR-0004', 'EG-004', 'WP-101'],
            array_map(
                static fn (RepositoryIndexEntry $entry): string => $entry->identifier,
                $index->all(),
            ),
        );
    }

    public function testRejectsDuplicateIdentifiersAndReportsPaths(): void
    {
        $index = new RepositoryIndex();
        $index->add($this->entry('EG-004', '/repo/first.md'));

        $this->expectException(DuplicateRepositoryEntryException::class);
        $this->expectExceptionMessage('/repo/first.md');
        $this->expectExceptionMessage('/repo/second.md');

        $index->add($this->entry('EG-004', '/repo/second.md'));
    }

    private function entry(string $identifier, string $path): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry(
            identifier: $identifier,
            title: $identifier,
            documentClass: 'NormativeDocument',
            category: 'Normative Specification',
            status: 'Draft',
            version: '0.1.0',
            path: $path,
        );
    }
}
