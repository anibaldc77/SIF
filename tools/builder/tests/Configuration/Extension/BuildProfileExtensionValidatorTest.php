<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Extension;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\Extension\BuildProfileExtensionValidator;
use Sif\Builder\Configuration\Extension\ExtensionCatalog;
use Sif\Builder\Configuration\Profile\ResolvedBuildProfile;

final class BuildProfileExtensionValidatorTest extends TestCase
{
    public function testAcceptsProfileWhoseExtensionsExistInCatalog(): void
    {
        $profile = new ResolvedBuildProfile(
            identifier: 'release',
            analyzers: ['metadata.completeness', 'reference.integrity'],
            generators: ['repository.index'],
            reporters: ['report.json'],
            strict: true,
        );

        $result = (new BuildProfileExtensionValidator())->validate(
            $profile,
            ExtensionCatalog::builtInDefault(),
            'D:/repository/.sif/builder.json',
        );

        self::assertTrue($result->isSuccessful());
        self::assertSame($profile, $result->profile);
        self::assertSame([], $result->diagnostics);
    }

    public function testReportsEveryUnknownExtensionInDeterministicCategoryOrder(): void
    {
        $profile = new ResolvedBuildProfile(
            identifier: 'custom',
            analyzers: ['unknown.analyzer', 'metadata.completeness'],
            generators: ['unknown.generator'],
            reporters: ['unknown.reporter'],
            strict: false,
        );

        $result = (new BuildProfileExtensionValidator())->validate(
            $profile,
            ExtensionCatalog::builtInDefault(),
            'D:/repository/.sif/builder.json',
        );

        self::assertFalse($result->isSuccessful());
        self::assertNull($result->profile);
        self::assertSame(
            ['CONFIG-109', 'CONFIG-110', 'CONFIG-111'],
            array_map(
                static fn ($diagnostic): string => $diagnostic->code,
                $result->diagnostics,
            ),
        );
        self::assertSame(
            ['unknown.analyzer', 'unknown.generator', 'unknown.reporter'],
            array_map(
                static fn ($diagnostic): mixed => $diagnostic->context['extension'],
                $result->diagnostics,
            ),
        );
    }

    public function testAllowsExplicitlyEmptyExtensionSelections(): void
    {
        $profile = new ResolvedBuildProfile(
            identifier: 'validation-only',
            analyzers: [],
            generators: [],
            reporters: [],
            strict: false,
        );

        $result = (new BuildProfileExtensionValidator())->validate(
            $profile,
            ExtensionCatalog::builtInDefault(),
        );

        self::assertTrue($result->isSuccessful());
        self::assertSame($profile, $result->profile);
    }
}
