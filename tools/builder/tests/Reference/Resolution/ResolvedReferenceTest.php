<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Resolution;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\Resolution\ResolvedReference;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ResolvedReferenceTest extends TestCase
{
    public function testAcceptsMatchingTarget(): void
    {
        $resolved = new ResolvedReference(new Reference('WP-102', 'ADR-001'), $this->entry('ADR-001'));

        self::assertSame('ADR-001', $resolved->target->identifier);
    }

    public function testRejectsMismatchedTarget(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ResolvedReference(new Reference('WP-102', 'ADR-001'), $this->entry('ADR-002'));
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
