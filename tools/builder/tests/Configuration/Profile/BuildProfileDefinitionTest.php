<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Profile;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\Profile\BuildProfileDefinition;

final class BuildProfileDefinitionTest extends TestCase
{
    public function testAcceptsAnImmutableProfileDefinition(): void
    {
        $profile = new BuildProfileDefinition(
            identifier: 'release',
            extends: 'base',
            analyzers: ['metadata.completeness'],
            strict: true,
        );

        self::assertSame('release', $profile->identifier);
        self::assertSame('base', $profile->extends);
        self::assertTrue($profile->strict);
    }

    public function testRejectsDuplicateExtensionIdentifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BuildProfileDefinition(
            identifier: 'release',
            analyzers: ['metadata.completeness', 'metadata.completeness'],
        );
    }
}
