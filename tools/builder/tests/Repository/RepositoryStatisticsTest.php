<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;
use Sif\Builder\Repository\RepositoryStatistics;

final class RepositoryStatisticsTest extends TestCase
{
    public function testCalculatesEmptyStatistics(): void
    {
        $statistics = new RepositoryStatistics(new RepositoryIndex());

        self::assertSame(0, $statistics->total);
        self::assertSame([], $statistics->byCategory);
        self::assertSame([], $statistics->byStatus);
        self::assertSame([], $statistics->byDocumentClass);
        self::assertSame([], $statistics->byWorkPackage);
    }

    public function testCalculatesAndSortsAllAggregates(): void
    {
        $index = new RepositoryIndex();
        $index->add($this->entry('WP-101', 'Work Package', 'Draft', 'WorkPackageDocument', 'WP-101'));
        $index->add($this->entry('ADR-002', 'Decision', 'Accepted', 'DecisionDocument', 'WP-101'));
        $index->add($this->entry('ES-004', 'Standard', 'Draft', 'NormativeDocument', null));

        $statistics = new RepositoryStatistics($index);

        self::assertSame(3, $statistics->total);
        self::assertSame(['Decision' => 1, 'Standard' => 1, 'Work Package' => 1], $statistics->byCategory);
        self::assertSame(['Accepted' => 1, 'Draft' => 2], $statistics->byStatus);
        self::assertSame(
            ['DecisionDocument' => 1, 'NormativeDocument' => 1, 'WorkPackageDocument' => 1],
            $statistics->byDocumentClass,
        );
        self::assertSame(['WP-101' => 2], $statistics->byWorkPackage);
    }

    private function entry(
        string $identifier,
        string $category,
        string $status,
        string $documentClass,
        ?string $workPackage,
    ): RepositoryIndexEntry {
        return new RepositoryIndexEntry(
            identifier: $identifier,
            title: $identifier,
            documentClass: $documentClass,
            category: $category,
            status: $status,
            version: '1.0.0',
            path: '/repo/' . $identifier . '.md',
            workPackage: $workPackage,
        );
    }
}
