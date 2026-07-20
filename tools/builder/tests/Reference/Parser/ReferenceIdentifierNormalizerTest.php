<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Parser;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Reference\Parser\ReferenceIdentifierNormalizer;

final class ReferenceIdentifierNormalizerTest extends TestCase
{
    public function testNormalizesIdentifierDeterministically(): void
    {
        $normalizer = new ReferenceIdentifierNormalizer();

        self::assertSame('ADR-001', $normalizer->normalize('  adr-001  '));
    }

    /** @dataProvider validIdentifiers */
    public function testAcceptsGovernedIdentifiers(string $identifier): void
    {
        self::assertTrue((new ReferenceIdentifierNormalizer())->isValid($identifier));
    }

    /** @return iterable<string, array{string}> */
    public static function validIdentifiers(): iterable
    {
        yield 'ADR' => ['ADR-001'];
        yield 'work package' => ['WP-102'];
        yield 'compound specification' => ['SPEC-WP-003-RUNTIME-FOUNDATION'];
        yield 'product document' => ['SIF-DP-001'];
    }

    /** @dataProvider invalidIdentifiers */
    public function testRejectsInvalidIdentifiers(string $identifier): void
    {
        self::assertFalse((new ReferenceIdentifierNormalizer())->isValid($identifier));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidIdentifiers(): iterable
    {
        yield 'empty' => [''];
        yield 'without separator' => ['ADR001'];
        yield 'leading separator' => ['-ADR-001'];
        yield 'space' => ['ADR 001'];
        yield 'lowercase not normalized' => ['adr-001'];
    }
}
