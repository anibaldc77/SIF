<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Exception\DuplicateReferenceException;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\ReferenceType;

final class ReferenceCollectionTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $collection = new ReferenceCollection();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testAddsAndReturnsReferencesDeterministically(): void
    {
        $collection = new ReferenceCollection();
        $collection->add(new Reference('B', 'C'));
        $collection->add(new Reference('A', 'D'));

        self::assertSame(['A', 'B'], array_map(
            static fn (Reference $reference): string => $reference->sourceIdentifier,
            $collection->all(),
        ));
    }

    public function testFiltersBySourceAndTarget(): void
    {
        $collection = new ReferenceCollection();
        $collection->add(new Reference('A', 'B'));
        $collection->add(new Reference('A', 'C', ReferenceType::RELATED));
        $collection->add(new Reference('D', 'B'));

        self::assertCount(2, $collection->bySource('A'));
        self::assertCount(2, $collection->byTarget('B'));
    }

    public function testRejectsDuplicates(): void
    {
        $collection = new ReferenceCollection();
        $reference = new Reference('A', 'B');
        $collection->add($reference);

        $this->expectException(DuplicateReferenceException::class);
        $collection->add($reference);
    }

    public function testRemovesReference(): void
    {
        $collection = new ReferenceCollection();
        $reference = new Reference('A', 'B');
        $collection->add($reference);

        self::assertTrue($collection->contains($reference));
        self::assertTrue($collection->remove($reference));
        self::assertFalse($collection->contains($reference));
        self::assertFalse($collection->remove($reference));
    }
}
