<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\JsonRepositoryConfigurationLoader;

final class JsonRepositoryConfigurationLoaderTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sif-builder-config-' . bin2hex(random_bytes(6));
        mkdir($this->root . DIRECTORY_SEPARATOR . '.sif', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testReturnsBackwardCompatibleDefaultWhenFileIsMissing(): void
    {
        $result = (new JsonRepositoryConfigurationLoader())->load($this->root);

        self::assertTrue($result->isSuccessful());
        self::assertSame('default', $result->configuration?->defaultProfile);
        self::assertCount(5, $result->configuration?->profiles['default']['analyzers'] ?? []);
    }

    public function testLoadsValidRepositoryConfiguration(): void
    {
        file_put_contents($this->root . '/.sif/builder.json', json_encode([
            'schema_version' => '1.0',
            'default_profile' => 'ci',
            'profiles' => [
                'ci' => [
                    'analyzers' => ['reference.integrity'],
                    'generators' => [],
                    'reporters' => ['report.json'],
                    'execution' => ['strict' => true],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $result = (new JsonRepositoryConfigurationLoader())->load($this->root);

        self::assertTrue($result->isSuccessful());
        self::assertSame('ci', $result->configuration?->defaultProfile);
    }

    public function testReportsInvalidJsonWithoutLeakingParserException(): void
    {
        file_put_contents($this->root . '/.sif/builder.json', '{invalid');

        $result = (new JsonRepositoryConfigurationLoader())->load($this->root);

        self::assertFalse($result->isSuccessful());
        self::assertSame('CONFIG-102', $result->diagnostics[0]->code);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . DIRECTORY_SEPARATOR . $entry;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}
