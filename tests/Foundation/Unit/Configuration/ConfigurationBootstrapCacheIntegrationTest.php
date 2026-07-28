<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Configuration;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Configuration\Bootstrap\ConfigurationBootstrapper;
use Sif\Foundation\Configuration\Cache\InMemoryConfigurationSnapshotCache;
use Sif\Foundation\Configuration\Source\ArrayConfigurationSource;
use Sif\Foundation\Configuration\Source\ConfigurationSourceResult;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;
use Sif\Foundation\Environment;

final class ConfigurationBootstrapCacheIntegrationTest extends TestCase
{
    public function testCacheMissComposesSourcesAndStoresSnapshot(): void
    {
        $cache = new InMemoryConfigurationSnapshotCache();
        $bootstrapper = new ConfigurationBootstrapper(
            [new ArrayConfigurationSource('defaults', ['app' => ['name' => 'SIF']])],
            $cache,
            'testing',
        );

        $result = $bootstrapper->load();

        self::assertFalse($result->cacheHit());
        self::assertSame('SIF', $result->snapshot()->repository()->string('app.name'));
        self::assertNotNull($cache->get('testing'));
        self::assertSame('CFG_BOOTSTRAP_CACHE_MISS', $result->diagnostics()[0]->code());
        self::assertArrayHasKey('app.name', $result->provenance());
    }

    public function testVerifiedCacheHitAvoidsLoadingSources(): void
    {
        $cache = new InMemoryConfigurationSnapshotCache();
        $source = new CountingConfigurationSource();
        $bootstrapper = new ConfigurationBootstrapper([$source], $cache, 'testing');

        $first = $bootstrapper->load();
        $second = $bootstrapper->load();

        self::assertFalse($first->cacheHit());
        self::assertTrue($second->cacheHit());
        self::assertSame(1, $source->loads);
        self::assertSame('CFG_BOOTSTRAP_CACHE_HIT', $second->diagnostics()[0]->code());
        self::assertSame([], $second->provenance());
    }

    public function testCacheEntriesCanBeForgottenAndCleared(): void
    {
        $cache = new InMemoryConfigurationSnapshotCache();
        $bootstrapper = new ConfigurationBootstrapper(
            [new ArrayConfigurationSource('defaults', ['app' => ['name' => 'SIF']])],
            $cache,
            'first',
        );
        $snapshot = $bootstrapper->load()->snapshot();
        $cache->put('second', $snapshot);

        $cache->forget('first');
        self::assertNull($cache->get('first'));
        self::assertNotNull($cache->get('second'));

        $cache->clear();
        self::assertNull($cache->get('second'));
    }

    public function testCacheRequiresAnExplicitNonEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConfigurationBootstrapper([], new InMemoryConfigurationSnapshotCache());
    }

    public function testBootstrapUsesConfigurationTwoPipelineWithoutChangingLifecycleSemantics(): void
    {
        $bootstrapper = new ConfigurationBootstrapper([
            new ArrayConfigurationSource('defaults', ['app' => ['name' => 'SIF']], 0),
            new ArrayConfigurationSource('environment', ['app' => ['debug' => true]], 100),
        ]);
        $application = (new Bootstrap(
            configurationBootstrapper: $bootstrapper,
        ))->createApplication(Environment::testing());

        self::assertSame('SIF', $application->configuration()->require('app.name'));
        self::assertTrue($application->configuration()->get('app.debug'));
        self::assertFalse($application->configuration()->isFrozen());

        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->configuration()->isFrozen());
    }
}

final class CountingConfigurationSource implements ConfigurationSourceInterface
{
    public int $loads = 0;

    public function id(): string
    {
        return 'counting';
    }

    public function type(): string
    {
        return 'test';
    }

    public function precedence(): int
    {
        return 0;
    }

    public function load(): ConfigurationSourceResult
    {
        ++$this->loads;

        return new ConfigurationSourceResult(
            'counting',
            'test',
            0,
            ['runtime' => ['workers' => 4]],
        );
    }
}
