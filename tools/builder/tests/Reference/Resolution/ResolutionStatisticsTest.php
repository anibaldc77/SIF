<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Resolution;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ResolutionResult;
use Sif\Builder\Reference\Resolution\ResolutionStatistics;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ResolutionStatisticsTest extends TestCase
{
    public function testBuildsCountsAndResolutionRate(): void
    {
        $implements = new Reference('WP-102', 'ADR-001', ReferenceType::IMPLEMENTS);
        $related = new Reference('WP-102', 'RFC-999', ReferenceType::RELATED);
        $result = new ResolutionResult(
            [new ResolvedReference($implements, $this->entry('ADR-001'))],
            [new BrokenReference($related)],
        );

        $statistics = new ResolutionStatistics($result);

        self::assertSame(2, $statistics->total);
        self::assertSame(1, $statistics->resolved);
        self::assertSame(1, $statistics->broken);
        self::assertSame(1, $statistics->byType['implements']);
        self::assertSame(1, $statistics->byType['related']);
        self::assertSame(0.5, $statistics->resolutionRate());
    }

    public function testEmptyResultHasFullResolutionRate(): void
    {
        self::assertSame(1.0, (new ResolutionStatistics(new ResolutionResult()))->resolutionRate());
    }

    private function entry(string $identifier): RepositoryIndexEntry
    {
        return new RepositoryIndexEntry(
            identifier: $identifier,
            title: $identifier,
            documentClass: 'EngineeringDocument',
            category: 'Engineering',
            status: 'Approved',
            version: '1.0.0',
            path: sprintf('/repo/%s.md', $identifier),
        );
    }
}
