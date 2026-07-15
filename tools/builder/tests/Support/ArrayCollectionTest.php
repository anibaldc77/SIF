<?php
declare(strict_types=1);
namespace Sif\Support\Tests;
use PHPUnit\Framework\TestCase;
use Sif\Support\Collections\ArrayCollection;
use Sif\Support\Exceptions\InvalidArgumentException;
final class ArrayCollectionTest extends TestCase
{
    public function testCollectionOperationsAreImmutable(): void { $collection = new ArrayCollection([1, 2, 3]); self::assertSame(1, $collection->first()); self::assertSame(3, $collection->last()); self::assertTrue($collection->contains(2)); self::assertSame([2, 3], $collection->filter(fn (int $item): bool => $item > 1)->toArray()); self::assertSame([2, 4, 6], $collection->map(fn (int $item): int => $item * 2)->toArray()); self::assertSame([1, 2, 3, 4], $collection->with(4)->toArray()); }
    public function testEmptyCollectionHasNoFirstValue(): void { $this->expectException(InvalidArgumentException::class); (new ArrayCollection())->first(); }
}
