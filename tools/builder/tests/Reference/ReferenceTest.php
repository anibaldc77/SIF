<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceType;

final class ReferenceTest extends TestCase
{
    public function testCreatesImmutableReference(): void
    {
        $reference = new Reference('SPEC-WP-003', 'ADR-001', ReferenceType::IMPLEMENTS, 12, 4, 'Implements ADR-001');

        self::assertSame('SPEC-WP-003', $reference->sourceIdentifier);
        self::assertSame('ADR-001', $reference->targetIdentifier);
        self::assertSame(ReferenceType::IMPLEMENTS, $reference->type);
        self::assertSame(12, $reference->line);
        self::assertSame(4, $reference->column);
    }

    public function testRejectsEmptyIdentifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Reference('', 'ADR-001');
    }

    public function testComparesReferencesByValue(): void
    {
        $left = new Reference('A', 'B', ReferenceType::RELATED, 1, 2, 'ctx');
        $right = new Reference('A', 'B', ReferenceType::RELATED, 1, 2, 'ctx');

        self::assertTrue($left->equals($right));
        self::assertSame($left->identity(), $right->identity());
    }
}
