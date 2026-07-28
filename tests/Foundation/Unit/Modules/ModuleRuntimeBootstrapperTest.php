<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Application;
use Sif\Foundation\Bootstrap;
use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\Source\ArrayConfigurationSource;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Contracts\ServiceProviderInterface;
use Sif\Foundation\Modules\Contribution\ModuleConfigurationNamespace;
use Sif\Foundation\Modules\Contribution\ModuleContributionSet;
use Sif\Foundation\Modules\Contracts\ModuleContributionProviderInterface;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Enablement\ExplicitModuleEnablementPolicy;
use Sif\Foundation\Modules\Enablement\ModuleEnablementDecision;
use Sif\Foundation\Modules\Exceptions\ModuleRuntimeIntegrationException;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleRegistry;
use Sif\Foundation\Modules\ModuleVersion;
use Sif\Foundation\Modules\Runtime\ModuleRuntimeBootstrapper;
use Sif\Foundation\ServiceProviderCollection;

final class ModuleRuntimeBootstrapperTest extends TestCase
{
    public function testIntegratesAllContributionTypesAndReturnsStableFingerprint(): void
    {
        $module = $this->module();
        $runtime = $this->runtime($module);
        $configuration = new ConfigurationRepository(['base' => ['value' => 'kept']]);
        $definitions = new ServiceDefinitionRegistry();
        $capabilities = new CapabilityRegistry();
        $providers = new ServiceProviderCollection();

        $result = $runtime->integrate($configuration, $definitions, $capabilities, $providers);

        self::assertSame('kept', $configuration->get('base.value'));
        self::assertSame(true, $configuration->get('feature.enabled'));
        self::assertTrue($definitions->has(new ServiceIdentifier('feature.service')));
        self::assertTrue($capabilities->has('feature.capability'));
        self::assertTrue($providers->has(RuntimeProvider::class));
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result->fingerprint());
        self::assertSame('MODULE_RUNTIME_INTEGRATED', $result->diagnostics()[0]->code());
    }

    public function testRequiredFrameworkCapabilityMayBeSatisfiedByExistingRegistry(): void
    {
        $module = $this->module(requiredCapabilities: ['foundation']);
        $capabilities = new CapabilityRegistry();
        $capabilities->register(new NamedCapability('foundation'));

        $result = $this->runtime($module)->integrate(
            new ConfigurationRepository(),
            new ServiceDefinitionRegistry(),
            $capabilities,
            new ServiceProviderCollection(),
        );

        self::assertSame('feature', $result->plan()->enabledModules()[0]->id()->value());
    }

    public function testMissingRequiredCapabilityRejectsIntegrationBeforeMutation(): void
    {
        $configuration = new ConfigurationRepository(['base' => 'unchanged']);
        $definitions = new ServiceDefinitionRegistry();
        $capabilities = new CapabilityRegistry();
        $providers = new ServiceProviderCollection();

        try {
            $this->runtime($this->module(requiredCapabilities: ['missing.capability']))->integrate(
                $configuration,
                $definitions,
                $capabilities,
                $providers,
            );
            self::fail('Expected runtime integration failure.');
        } catch (ModuleRuntimeIntegrationException) {
            self::assertSame(['base' => 'unchanged'], $configuration->all());
            self::assertSame([], $definitions->all());
            self::assertSame([], $capabilities->all());
            self::assertTrue($providers->isEmpty());
        }
    }

    public function testRequiredCapabilityMayBeProvidedByAnotherEnabledModule(): void
    {
        $provider = $this->module('provider', 'shared.capability');
        $consumer = $this->module(
            'consumer',
            'consumer.capability',
            ['shared.capability'],
            AlternateRuntimeProvider::class,
        );
        $registry = new ModuleRegistry();
        $registry->register($provider);
        $registry->register($consumer);
        $policy = new ExplicitModuleEnablementPolicy([], ModuleEnablementDecision::enabled());

        $result = (new ModuleRuntimeBootstrapper($registry, $policy))->integrate(
            new ConfigurationRepository(),
            new ServiceDefinitionRegistry(),
            new CapabilityRegistry(),
            new ServiceProviderCollection(),
        );

        self::assertCount(2, $result->plan()->enabledModules());
    }

    public function testProviderWithRequiredConstructorArgumentsIsRejected(): void
    {
        $module = $this->module(provider: InvalidRuntimeProvider::class);

        $this->expectException(ModuleRuntimeIntegrationException::class);
        $this->runtime($module)->integrate(
            new ConfigurationRepository(),
            new ServiceDefinitionRegistry(),
            new CapabilityRegistry(),
            new ServiceProviderCollection(),
        );
    }

    public function testBootstrapWithoutModuleRuntimePreservesHistoricalPath(): void
    {
        $application = (new Bootstrap())->createApplication($this->environment());

        self::assertInstanceOf(Application::class, $application);
        self::assertNull($application->moduleRuntime());
        self::assertSame([], $application->serviceDefinitions()->all());
    }

    public function testBootstrapPublishesModuleRuntimeStateOnApplication(): void
    {
        $application = (new Bootstrap(
            moduleRuntimeBootstrapper: $this->runtime($this->module(requiredCapabilities: ['foundation'])),
        ))->createApplication($this->environment());

        self::assertInstanceOf(Application::class, $application);
        self::assertNotNull($application->moduleRuntime());
        self::assertTrue($application->hasCapability('feature.capability'));
        self::assertTrue($application->providers()->has(RuntimeProvider::class));
        self::assertTrue($application->serviceDefinitions()->has(new ServiceIdentifier('feature.service')));
    }

    public function testEquivalentPlansProduceEquivalentFingerprints(): void
    {
        $first = $this->integrateFresh($this->module());
        $second = $this->integrateFresh($this->module());

        self::assertSame($first, $second);
    }

    private function integrateFresh(ModuleInterface $module): string
    {
        return $this->runtime($module)->integrate(
            new ConfigurationRepository(),
            new ServiceDefinitionRegistry(),
            new CapabilityRegistry(),
            new ServiceProviderCollection(),
        )->fingerprint();
    }

    private function runtime(ModuleInterface $module): ModuleRuntimeBootstrapper
    {
        $registry = new ModuleRegistry();
        $registry->register($module);

        return new ModuleRuntimeBootstrapper(
            $registry,
            new ExplicitModuleEnablementPolicy([], ModuleEnablementDecision::enabled()),
        );
    }

    /**
     * @param list<non-empty-string> $requiredCapabilities
     * @param class-string<ServiceProviderInterface> $provider
     */
    private function module(
        string $id = 'feature',
        string $providedCapability = 'feature.capability',
        array $requiredCapabilities = [],
        string $provider = RuntimeProvider::class,
    ): ModuleInterface {
        return new class ($id, $providedCapability, $requiredCapabilities, $provider) implements ModuleInterface, ModuleContributionProviderInterface {
            /**
             * @param list<non-empty-string> $requiredCapabilities
             * @param class-string<ServiceProviderInterface> $provider
             */
            public function __construct(
                private string $id,
                private string $providedCapability,
                private array $requiredCapabilities,
                private string $provider,
            ) {
            }

            public function descriptor(): ModuleDescriptor
            {
                /** @var non-empty-string $providedCapability */
                $providedCapability = $this->providedCapability;

                return new ModuleDescriptor(
                    new ModuleId($this->id),
                    new ModuleVersion('1.0.0'),
                    ucfirst($this->id),
                    requiredCapabilities: $this->requiredCapabilities,
                    providedCapabilities: [$providedCapability],
                    configurationNamespace: $this->id,
                    serviceProviders: [$this->provider],
                );
            }

            public function contributions(): ModuleContributionSet
            {
                return new ModuleContributionSet(
                    new ModuleConfigurationNamespace($this->id),
                    [new ArrayConfigurationSource($this->id . '.source', [
                        $this->id => ['enabled' => true],
                    ])],
                    [ServiceDefinition::forInstance(
                        new ServiceIdentifier($this->id . '.service'),
                        new \stdClass(),
                    )],
                    [new NamedCapability($this->providedCapability)],
                );
            }
        };
    }

    private function environment(): EnvironmentInterface
    {
        return new class implements EnvironmentInterface {
            public function name(): string
            {
                return 'testing';
            }

            public function isDevelopment(): bool { return false; }
            public function isTesting(): bool { return true; }
            public function isStaging(): bool { return false; }
            public function isProduction(): bool { return false; }
            public function equals(EnvironmentInterface $other): bool { return $other->name() === $this->name(); }
            public function __toString(): string { return $this->name(); }
        };
    }
}

final class RuntimeProvider implements ServiceProviderInterface
{
    public function register(ApplicationInterface $application): void {}
    public function boot(ApplicationInterface $application): void {}
    public function shutdown(ApplicationInterface $application): void {}
}

final class AlternateRuntimeProvider implements ServiceProviderInterface
{
    public function register(ApplicationInterface $application): void {}
    public function boot(ApplicationInterface $application): void {}
    public function shutdown(ApplicationInterface $application): void {}
}

final class InvalidRuntimeProvider implements ServiceProviderInterface
{
    public function __construct(string $required) { if ($required === '') { throw new \InvalidArgumentException(); } }
    public function register(ApplicationInterface $application): void {}
    public function boot(ApplicationInterface $application): void {}
    public function shutdown(ApplicationInterface $application): void {}
}
