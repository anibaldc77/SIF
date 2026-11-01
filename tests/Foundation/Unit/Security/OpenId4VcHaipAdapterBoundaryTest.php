<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;

final class OpenId4VcHaipAdapterBoundaryTest extends TestCase
{
    public function testComposerRegistersAdapterNamespace(): void
    {
        $root = dirname(__DIR__, 4);
        $loader = require $root . '/vendor/autoload.php';

        self::assertInstanceOf(ClassLoader::class, $loader);

        $prefixes = $loader->getPrefixesPsr4();

        self::assertArrayHasKey('Sif\\Adapters\\', $prefixes);

        $expected = realpath($root . '/src/Adapters');
        $actual = realpath($prefixes['Sif\\Adapters\\'][0]);

        self::assertNotFalse($expected);
        self::assertNotFalse($actual);
        self::assertSame($expected, $actual);
    }

    public function testPhpStanIncludesAdapterDirectory(): void
    {
        $root = dirname(__DIR__, 4);
        $configuration = file_get_contents(
            $root . '/phpstan.neon'
        );

        self::assertIsString($configuration);
        self::assertMatchesRegularExpression(
            '/(?m)^\s*-\s*src\/Adapters\s*$/',
            $configuration
        );
    }
}
