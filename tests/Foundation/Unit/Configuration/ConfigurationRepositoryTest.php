<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\Exceptions\ConfigurationNotFoundException;
use Sif\Foundation\Configuration\Exceptions\FrozenConfigurationException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationKeyException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationStructureException;

final class ConfigurationRepositoryTest extends TestCase
{
    public function testItReadsTopLevelAndNestedValues(): void
    {
        $configuration = new ConfigurationRepository([
            'app' => ['name' => 'SIF'],
            'debug' => true,
        ]);

        self::assertSame('SIF', $configuration->get('app.name'));
        self::assertTrue($configuration->get('debug'));
        self::assertTrue($configuration->has('app.name'));
    }

    public function testItReturnsDefaultForMissingValues(): void
    {
        $configuration = new ConfigurationRepository();

        self::assertSame('fallback', $configuration->get('app.name', 'fallback'));
        self::assertFalse($configuration->has('app.name'));
    }

    public function testNullIsADefinedValue(): void
    {
        $configuration = new ConfigurationRepository(['cache' => null]);

        self::assertTrue($configuration->has('cache'));
        self::assertNull($configuration->get('cache', 'fallback'));
        self::assertNull($configuration->require('cache'));
    }

    public function testRequireReturnsDefinedValue(): void
    {
        $configuration = new ConfigurationRepository(['app' => ['name' => 'SIF']]);

        self::assertSame('SIF', $configuration->require('app.name'));
    }

    public function testRequireRejectsMissingValue(): void
    {
        $configuration = new ConfigurationRepository();

        $this->expectException(ConfigurationNotFoundException::class);
        $this->expectExceptionMessage('Configuration key "database.default" is not defined.');

        $configuration->require('database.default');
    }

    public function testItSetsNestedValuesAndCreatesMissingArrays(): void
    {
        $configuration = new ConfigurationRepository();

        $configuration->set('database.connections.default.driver', 'pdo');

        self::assertSame('pdo', $configuration->get('database.connections.default.driver'));
        self::assertSame([
            'database' => [
                'connections' => [
                    'default' => ['driver' => 'pdo'],
                ],
            ],
        ], $configuration->all());
    }

    public function testItRejectsWritingThroughAScalarValue(): void
    {
        $configuration = new ConfigurationRepository(['database' => 'disabled']);

        $this->expectException(InvalidConfigurationStructureException::class);
        $this->expectExceptionMessage('segment "database" is not an array');

        $configuration->set('database.default', 'main');
    }

    public function testItReplacesTheCompleteConfiguration(): void
    {
        $configuration = new ConfigurationRepository(['old' => true]);

        $configuration->replace(['new' => ['value' => 42]]);

        self::assertFalse($configuration->has('old'));
        self::assertSame(42, $configuration->get('new.value'));
    }

    public function testFreezeIsIdempotentAndPreventsSet(): void
    {
        $configuration = new ConfigurationRepository();
        $configuration->freeze();
        $configuration->freeze();

        self::assertTrue($configuration->isFrozen());

        $this->expectException(FrozenConfigurationException::class);
        $configuration->set('app.name', 'SIF');
    }

    public function testFrozenConfigurationCannotBeReplaced(): void
    {
        $configuration = new ConfigurationRepository(['app' => 'SIF']);
        $configuration->freeze();

        $this->expectException(FrozenConfigurationException::class);
        $configuration->replace([]);
    }

    /**
     * @dataProvider invalidKeyProvider
     */
    public function testItRejectsInvalidKeys(string $key): void
    {
        $configuration = new ConfigurationRepository();

        $this->expectException(InvalidConfigurationKeyException::class);
        $configuration->get($key);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidKeyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace' => ['   '];
        yield 'empty middle segment' => ['app..name'];
        yield 'empty leading segment' => ['.app'];
        yield 'empty trailing segment' => ['app.'];
    }

    public function testItTrimsOuterWhitespaceFromKeys(): void
    {
        $configuration = new ConfigurationRepository(['app' => ['name' => 'SIF']]);

        self::assertSame('SIF', $configuration->get('  app.name  '));
    }

    public function testAllReturnsTheCompleteConfigurationTree(): void
    {
        $values = ['app' => ['name' => 'SIF'], 'ports' => [80, 443]];
        $configuration = new ConfigurationRepository($values);

        self::assertSame($values, $configuration->all());
    }
}
