<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ResolutionPath;
use Sif\Foundation\Container\ServiceIdentifier;

final class ResolutionPathTest extends TestCase
{
    public function testPathIsImmutableAndOrdered(): void
    {
        $base = new ResolutionPath();
        $first = $base->append(new ServiceIdentifier('first'));
        $second = $first->append(new ServiceIdentifier('second'));

        self::assertTrue($base->isEmpty());
        self::assertSame(1, $first->count());
        self::assertSame(2, $second->count());
        self::assertSame('first -> second', $second->format());
    }

    public function testPathDetectsIdentifierByValue(): void
    {
        $path = new ResolutionPath([
            new ServiceIdentifier('first'),
            new ServiceIdentifier('second'),
        ]);

        self::assertTrue(
            $path->contains(new ServiceIdentifier('second')),
        );
        self::assertFalse(
            $path->contains(new ServiceIdentifier('missing')),
        );
    }
}
