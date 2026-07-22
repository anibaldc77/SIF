<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Extension;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\Extension\ExtensionCatalog;

final class ExtensionCatalogTest extends TestCase
{
    public function testExposesBuiltInExtensionsInGovernedOrder(): void
    {
        $catalog = ExtensionCatalog::builtInDefault();

        self::assertSame([
            'metadata.completeness',
            'reference.integrity',
            'document.consistency',
            'repository.policy',
            'generated.artifacts',
        ], $catalog->analyzers);
        self::assertSame([
            'repository.index',
            'reference.report',
            'reference.graph',
            'repository.manifest',
            'documentation.navigation',
        ], $catalog->generators);
        self::assertSame(['report.markdown', 'report.json'], $catalog->reporters);
    }

    public function testRejectsDuplicateIdentifiersInsideCategory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExtensionCatalog(
            analyzers: ['metadata.completeness', 'metadata.completeness'],
            generators: [],
            reporters: [],
        );
    }

    public function testRejectsMalformedIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExtensionCatalog(
            analyzers: [],
            generators: ['Repository Index'],
            reporters: [],
        );
    }
}
