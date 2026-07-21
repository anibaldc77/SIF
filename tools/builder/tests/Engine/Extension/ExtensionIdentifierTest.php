<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Extension;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Exception\InvalidExtensionIdentifierException;
use Sif\Builder\Engine\Extension\ExtensionIdentifier;

final class ExtensionIdentifierTest extends TestCase
{
    public function testNormalizesValidIdentifier(): void
    {
        self::assertSame('reference.broken', ExtensionIdentifier::normalize('  REFERENCE.BROKEN  '));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidIdentifiers(): iterable
    {
        yield 'empty' => [''];
        yield 'hyphen' => ['reference-broken'];
        yield 'underscore' => ['reference_broken'];
        yield 'leading dot' => ['.reference'];
        yield 'trailing dot' => ['reference.'];
        yield 'empty segment' => ['reference..broken'];
        yield 'space' => ['reference broken'];
    }

    #[DataProvider('invalidIdentifiers')]
    public function testRejectsInvalidIdentifier(string $identifier): void
    {
        $this->expectException(InvalidExtensionIdentifierException::class);
        ExtensionIdentifier::normalize($identifier);
    }
}
