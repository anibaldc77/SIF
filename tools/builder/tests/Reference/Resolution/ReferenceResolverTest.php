<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Resolution;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\ReferenceType;
use Sif\Builder\Reference\Resolution\BrokenReference;
use Sif\Builder\Reference\Resolution\ReferenceResolver;
use Sif\Builder\Repository\RepositoryIndex;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class ReferenceResolverTest extends TestCase
{
    public function testResolvesExistingTargetsAndReportsMissingTargets(): void
    {
        $references = new ReferenceCollection();
        $references->add(new Reference('WP-102', 'ADR-001', ReferenceType::IMPLEMENTS));
        $references->add(new Reference('WP-102', 'RFC-999', ReferenceType::REFERENCE));

        $index = new RepositoryIndex();
        $index->add($this->entry('ADR-001'));

        $result = (new ReferenceResolver())->resolve($references, $index);

        self::assertSame(2, $result->total());
        self::assertSame(1, $result->resolvedCount());
        self::assertSame(1, $result->brokenCount());
        self::assertFalse($result->isSuccessful());
        self::assertSame('ADR-001', $result->resolved[0]->target->identifier);
        self::assertSame('RFC-999', $result->broken[0]->reference->targetIdentifier);
        self::assertSame(BrokenReference::TARGET_NOT_FOUND, $result->broken[0]->reason);
    }

    public function testEmptyCollectionProducesSuccessfulEmptyResult(): void
    {
        $result = (new ReferenceResolver())->resolve(new ReferenceCollection(), new RepositoryIndex());

        self::assertTrue($result->isEmpty());
        self::assertTrue($result->isSuccessful());
        self::assertSame(0, $result->total());
    }

    public function testResultOrderingIsDeterministic(): void
    {
        $references = new ReferenceCollection();
        $references->add(new Reference('WP-102', 'RFC-002'));
        $references->add(new Reference('WP-102', 'ADR-001'));

        $index = new RepositoryIndex();
        $index->add($this->entry('RFC-002'));
        $index->add($this->entry('ADR-001'));

        $result = (new ReferenceResolver())->resolve($references, $index);

        self::assertSame(
            ['ADR-001', 'RFC-002'],
            array_map(
                static fn ($item): string => $item->target->identifier,
                $result->resolved,
            ),
        );
    }

    public function testResolutionDependsOnlyOnTargetExistence(): void
    {
        $references = new ReferenceCollection();
        $references->add(new Reference('MISSING-SOURCE', 'ADR-001'));

        $index = new RepositoryIndex();
        $index->add($this->entry('ADR-001'));

        $result = (new ReferenceResolver())->resolve($references, $index);

        self::assertTrue($result->isSuccessful());
        self::assertSame(1, $result->resolvedCount());
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
