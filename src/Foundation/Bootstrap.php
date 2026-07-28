<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Configuration\Bootstrap\ConfigurationBootstrapper;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\Loader\ConfigurationFileLoader;
use Sif\Foundation\Contracts\BootstrapInterface;
use Sif\Foundation\Contracts\EnvironmentAwareApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Environment\CompositeEnvironmentProvider;
use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\DotenvEnvironmentProvider;
use Sif\Foundation\Environment\EnvironmentRepository;
use Sif\Foundation\Environment\NativeEnvironmentProvider;

final class Bootstrap implements BootstrapInterface
{
    private ConfigurationFileLoader $configurationLoader;

    /** @var list<string> */
    private array $configurationSources;

    private EnvironmentProviderInterface $nativeEnvironment;

    private ?string $dotenvSource;

    private ?ConfigurationBootstrapper $configurationBootstrapper;

    /**
     * Sources are processed from lowest to highest precedence.
     *
     * @param iterable<string> $configurationSources
     */
    public function __construct(
        ?ConfigurationFileLoader $configurationLoader = null,
        iterable $configurationSources = [],
        ?EnvironmentProviderInterface $nativeEnvironment = null,
        ?string $dotenvSource = null,
        ?ConfigurationBootstrapper $configurationBootstrapper = null,
    ) {
        $this->configurationLoader = $configurationLoader
            ?? ConfigurationFileLoader::withDefaultLoaders();
        $this->configurationSources = [];
        $this->nativeEnvironment = $nativeEnvironment ?? new NativeEnvironmentProvider();
        $this->dotenvSource = $dotenvSource;
        $this->configurationBootstrapper = $configurationBootstrapper;

        foreach ($configurationSources as $source) {
            $this->configurationSources[] = $source;
        }
    }
    public function createApplication(EnvironmentInterface $environment): EnvironmentAwareApplicationInterface
    {
        $lifecycle = new Lifecycle();
        $providers = new ServiceProviderCollection();
        $kernel = new Kernel($lifecycle);
        $variables = $this->createEnvironmentRepository();
        $runtime = new Runtime($variables);
        $configurationValues = $this->configurationBootstrapper !== null
            ? $this->configurationBootstrapper->load()->snapshot()->values()
            : $this->configurationLoader->loadMany($this->configurationSources);
        $configuration = new ConfigurationRepository($configurationValues);

        return new Application(
            $runtime,
            $kernel,
            $environment,
            $providers,
            new CapabilityRegistry(),
            $configuration,
            $variables,
        );
    }

    private function createEnvironmentRepository(): EnvironmentRepository
    {
        if ($this->dotenvSource === null) {
            return new EnvironmentRepository($this->nativeEnvironment);
        }

        $dotenv = DotenvEnvironmentProvider::fromFile(
            $this->dotenvSource,
            $this->nativeEnvironment,
        );

        return new EnvironmentRepository(new CompositeEnvironmentProvider(
            $dotenv,
            $this->nativeEnvironment,
        ));
    }
}
