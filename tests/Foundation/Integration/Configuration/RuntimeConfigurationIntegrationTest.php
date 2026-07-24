<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Configuration\Exceptions\FrozenConfigurationException;
use Sif\Foundation\Configuration\Loader\ConfigurationFileLoader;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\ConfigurationAwareApplicationInterface;
use Sif\Foundation\Framework;
use Sif\Foundation\ServiceProvider;

final class RuntimeConfigurationIntegrationTest extends TestCase
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

        $this->temporaryFiles = [];
    }

    public function testDefaultApplicationExposesEmptyMutableConfigurationBeforeBoot(): void
    {
        $application = $this->application();

        self::assertInstanceOf(ConfigurationAwareApplicationInterface::class, $application);
        self::assertSame([], $application->configuration()->all());
        self::assertFalse($application->configuration()->isFrozen());
        self::assertTrue($application->hasCapability('configuration'));
    }

    public function testBootstrapLoadsConfigurationSourcesUsingDeclaredPrecedence(): void
    {
        $base = $this->phpSource([
            'app' => ['name' => 'SIF', 'debug' => false],
            'middleware' => ['first', 'second'],
        ]);
        $override = $this->jsonSource([
            'app' => ['debug' => true],
            'middleware' => ['replacement'],
        ]);

        $application = $this->bootstrap([$base, $override]);

        self::assertSame('SIF', $application->configuration()->get('app.name'));
        self::assertTrue($application->configuration()->get('app.debug'));
        self::assertSame(['replacement'], $application->configuration()->get('middleware'));
    }

    public function testProvidersCanReadAndMutateConfigurationDuringRegisterAndBoot(): void
    {
        $application = $this->bootstrap([$this->phpSource(['app' => ['name' => 'SIF']])]);
        $provider = new ConfigurationRecordingProvider();
        $application->providers()->add($provider);

        $result = $application->boot();

        self::assertTrue($result->succeeded());
        self::assertSame(['register:SIF', 'boot:enabled'], $provider->operations);
        self::assertTrue($application->configuration()->get('feature.enabled'));
    }

    public function testConfigurationIsFrozenAfterSuccessfulBoot(): void
    {
        $application = $this->application();

        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->configuration()->isFrozen());

        $this->expectException(FrozenConfigurationException::class);
        $application->configuration()->set('app.name', 'changed');
    }

    public function testConfigurationRemainsReadableAfterSuccessfulBoot(): void
    {
        $application = $this->bootstrap([$this->jsonSource(['app' => ['name' => 'SIF']])]);

        self::assertTrue($application->boot()->succeeded());
        self::assertSame('SIF', $application->configuration()->require('app.name'));
    }

    public function testFailedProviderBootDoesNotFreezeConfiguration(): void
    {
        $application = $this->application();
        $application->providers()->add(new FailingConfigurationProvider());

        $result = $application->boot();

        self::assertTrue($result->failed());
        self::assertFalse($application->configuration()->isFrozen());
        $application->configuration()->set('diagnostics.boot_failed', true);
        self::assertTrue($application->configuration()->get('diagnostics.boot_failed'));
    }

    public function testApplicationsKeepIndependentConfigurationRepositories(): void
    {
        $first = $this->application();
        $second = $this->application();

        $first->configuration()->set('app.name', 'first');

        self::assertSame('first', $first->configuration()->get('app.name'));
        self::assertFalse($second->configuration()->has('app.name'));
    }

    public function testBootstrapAcceptsAnEmptySourceList(): void
    {
        $application = $this->bootstrap([]);

        self::assertSame([], $application->configuration()->all());
        self::assertTrue($application->boot()->succeeded());
        self::assertTrue($application->configuration()->isFrozen());
    }

    private function application(): ConfigurationAwareApplicationInterface
    {
        $application = Framework::create();
        self::assertInstanceOf(ConfigurationAwareApplicationInterface::class, $application);

        return $application;
    }

    /**
     * @param iterable<string> $sources
     */
    private function bootstrap(iterable $sources): ConfigurationAwareApplicationInterface
    {
        $environment = Framework::create()->environment();
        $bootstrap = new Bootstrap(
            ConfigurationFileLoader::withDefaultLoaders(),
            $sources,
        );
        $application = $bootstrap->createApplication($environment);
        self::assertInstanceOf(ConfigurationAwareApplicationInterface::class, $application);

        return $application;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function phpSource(array $values): string
    {
        $file = $this->temporaryFile('.php');
        file_put_contents($file, '<?php return ' . var_export($values, true) . ';');

        return $file;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function jsonSource(array $values): string
    {
        $file = $this->temporaryFile('.json');
        file_put_contents($file, json_encode($values, JSON_THROW_ON_ERROR));

        return $file;
    }

    private function temporaryFile(string $suffix): string
    {
        $file = tempnam(sys_get_temp_dir(), 'sif-configuration-');

        if ($file === false) {
            self::fail('Unable to create a temporary configuration source.');
        }

        $renamed = $file . $suffix;
        rename($file, $renamed);
        $this->temporaryFiles[] = $renamed;

        return $renamed;
    }
}

final class ConfigurationRecordingProvider extends ServiceProvider
{
    /** @var list<string> */
    public array $operations = [];

    public function register(ApplicationInterface $application): void
    {
        if (!$application instanceof ConfigurationAwareApplicationInterface) {
            return;
        }

        $this->operations[] = 'register:' . $application->configuration()->require('app.name');
        $application->configuration()->set('feature.enabled', true);
    }

    public function boot(ApplicationInterface $application): void
    {
        if (!$application instanceof ConfigurationAwareApplicationInterface) {
            return;
        }

        $this->operations[] = 'boot:'
            . ($application->configuration()->get('feature.enabled') ? 'enabled' : 'disabled');
    }
}

final class FailingConfigurationProvider extends ServiceProvider
{
    public function register(ApplicationInterface $application): void
    {
        // Intentionally empty: this provider fails only during boot.
    }

    public function boot(ApplicationInterface $application): void
    {
        throw new \RuntimeException('Provider boot failed.');
    }
}
