<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\ReferenceTarget;

final class ReferenceTargetTest extends TestCase
{
    public function testCreatesUnresolvedTarget(): void
    {
        $target = new ReferenceTarget('ADR-001');

        self::assertFalse($target->exists);
        self::assertFalse($target->resolved);
    }

    public function testCreatesResolvedTarget(): void
    {
        $target = new ReferenceTarget('ADR-001', true, true);

        self::assertTrue($target->exists);
        self::assertTrue($target->resolved);
    }

    public function testRejectsResolvedMissingTarget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ReferenceTarget('ADR-001', false, true);
    }
}
