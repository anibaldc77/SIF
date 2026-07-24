<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Configuration\Loader;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\Exceptions\ConfigurationSourceNotFoundException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceException;
use Sif\Foundation\Configuration\Exceptions\UnsupportedConfigurationSourceException;
use Sif\Foundation\Configuration\Loader\ConfigurationFileLoader;
use Sif\Foundation\Configuration\Loader\ConfigurationMerger;
use Sif\Foundation\Configuration\Loader\JsonConfigurationLoader;
use Sif\Foundation\Configuration\Loader\PhpConfigurationLoader;

final class ConfigurationLoadersTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testPhpLoaderLoadsAnArray(): void
    {
        $file = $this->temporaryFile('php', "<?php\nreturn ['app' => ['name' => 'SIF']];\n");
        $loader = new PhpConfigurationLoader();

        self::assertTrue($loader->supports($file));
        self::assertSame(['app' => ['name' => 'SIF']], $loader->load($file));
    }

    public function testPhpLoaderRejectsNonArrayResult(): void
    {
        $file = $this->temporaryFile('php', "<?php\nreturn 'invalid';\n");

        $this->expectException(InvalidConfigurationSourceException::class);
        $this->expectExceptionMessage('must produce an array');

        (new PhpConfigurationLoader())->load($file);
    }

    public function testPhpLoaderWrapsExecutionFailure(): void
    {
        $file = $this->temporaryFile('php', "<?php\nthrow new RuntimeException('broken source');\n");

        $this->expectException(InvalidConfigurationSourceException::class);
        $this->expectExceptionMessage('broken source');

        (new PhpConfigurationLoader())->load($file);
    }

    public function testJsonLoaderLoadsAnObjectAsArray(): void
    {
        $file = $this->temporaryFile('json', '{"app":{"name":"SIF"},"debug":true}');
        $loader = new JsonConfigurationLoader();

        self::assertTrue($loader->supports(strtoupper($file)));
        self::assertSame([
            'app' => ['name' => 'SIF'],
            'debug' => true,
        ], $loader->load($file));
    }

    public function testJsonLoaderRejectsMalformedJson(): void
    {
        $file = $this->temporaryFile('json', '{"app":');

        $this->expectException(InvalidConfigurationSourceException::class);
        $this->expectExceptionMessage('contains invalid JSON');

        (new JsonConfigurationLoader())->load($file);
    }

    public function testJsonLoaderRejectsScalarRoot(): void
    {
        $file = $this->temporaryFile('json', 'true');

        $this->expectException(InvalidConfigurationSourceException::class);
        $this->expectExceptionMessage('must produce an array');

        (new JsonConfigurationLoader())->load($file);
    }

    public function testMissingSourceFailsExplicitly(): void
    {
        $source = sys_get_temp_dir() . '/sif-missing-' . bin2hex(random_bytes(8)) . '.php';

        $this->expectException(ConfigurationSourceNotFoundException::class);
        $this->expectExceptionMessage('does not exist');

        (new PhpConfigurationLoader())->load($source);
    }

    public function testMergerRecursivelyCombinesAssociativeArrays(): void
    {
        $merger = new ConfigurationMerger();

        $result = $merger->merge(
            [
                'database' => [
                    'default' => 'main',
                    'connections' => [
                        'main' => ['driver' => 'pdo', 'host' => 'localhost'],
                    ],
                ],
            ],
            [
                'database' => [
                    'connections' => [
                        'main' => ['host' => 'database.internal'],
                    ],
                ],
            ],
        );

        self::assertSame([
            'database' => [
                'default' => 'main',
                'connections' => [
                    'main' => [
                        'driver' => 'pdo',
                        'host' => 'database.internal',
                    ],
                ],
            ],
        ], $result);
    }

    public function testLaterScalarReplacesEarlierArray(): void
    {
        $result = (new ConfigurationMerger())->merge(
            ['cache' => ['driver' => 'file']],
            ['cache' => false],
        );

        self::assertSame(['cache' => false], $result);
    }

    public function testLaterArrayReplacesEarlierScalar(): void
    {
        $result = (new ConfigurationMerger())->merge(
            ['cache' => false],
            ['cache' => ['driver' => 'file']],
        );

        self::assertSame(['cache' => ['driver' => 'file']], $result);
    }

    public function testListsAreReplacedRatherThanMergedByIndex(): void
    {
        $result = (new ConfigurationMerger())->merge(
            ['middleware' => ['first', 'second']],
            ['middleware' => ['replacement']],
        );

        self::assertSame(['middleware' => ['replacement']], $result);
    }

    public function testEmptyOverrideArrayReplacesAssociativeMap(): void
    {
        $result = (new ConfigurationMerger())->merge(
            ['services' => ['mail' => true]],
            ['services' => []],
        );

        self::assertSame(['services' => []], $result);
    }

    public function testFileLoaderSelectsDefaultLoaders(): void
    {
        $php = $this->temporaryFile('php', "<?php\nreturn ['app' => ['name' => 'SIF']];\n");
        $json = $this->temporaryFile('json', '{"debug":true}');
        $loader = ConfigurationFileLoader::withDefaultLoaders();

        self::assertSame(['app' => ['name' => 'SIF']], $loader->load($php));
        self::assertSame(['debug' => true], $loader->load($json));
    }

    public function testFileLoaderRejectsUnsupportedExtension(): void
    {
        $file = $this->temporaryFile('yaml', 'app: SIF');

        $this->expectException(UnsupportedConfigurationSourceException::class);
        $this->expectExceptionMessage('No configuration loader supports');

        ConfigurationFileLoader::withDefaultLoaders()->load($file);
    }

    public function testLoadManyAppliesLaterSourcePrecedence(): void
    {
        $base = $this->temporaryFile('php', <<<'PHPFILE'
<?php
return [
    'app' => ['name' => 'SIF', 'debug' => false],
    'ports' => [80, 443],
];
PHPFILE);
        $environment = $this->temporaryFile('json', <<<'JSON'
{
    "app": {"debug": true},
    "ports": [8080]
}
JSON);

        $result = ConfigurationFileLoader::withDefaultLoaders()->loadMany([
            $base,
            $environment,
        ]);

        self::assertSame([
            'app' => ['name' => 'SIF', 'debug' => true],
            'ports' => [8080],
        ], $result);
    }

    public function testLoadManyWithNoSourcesReturnsEmptyConfiguration(): void
    {
        self::assertSame([], ConfigurationFileLoader::withDefaultLoaders()->loadMany([]));
    }

    private function temporaryFile(string $extension, string $contents): string
    {
        $file = sys_get_temp_dir()
            . '/sif-configuration-'
            . bin2hex(random_bytes(8))
            . '.'
            . $extension;

        file_put_contents($file, $contents);
        $this->temporaryFiles[] = $file;

        return $file;
    }
}
