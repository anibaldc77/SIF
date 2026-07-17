<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Repository;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexIssue;
use Sif\Builder\Repository\RepositoryIndexingResult;

final class RepositoryIndexingResultTest extends TestCase
{
    public function testExposesIndexIssuesAndCounters(): void
    {
        $result = new RepositoryIndexingResult(
            new RepositoryIndex(),
            [new RepositoryIndexIssue('/invalid.md', 'Invalid document.')],
            0.25,
        );

        self::assertFalse($result->isSuccessful());
        self::assertSame(0, $result->indexedCount());
        self::assertSame(1, $result->issueCount());
        self::assertSame(0.25, $result->durationSeconds);
    }

    public function testRejectsNegativeDuration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RepositoryIndexingResult(new RepositoryIndex(), [], -0.01);
    }
}
