<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Configuration\Bootstrap\ConfigurationBootstrapper;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\Loader\ConfigurationFileLoader;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Contracts\BootstrapInterface;
use Sif\Foundation\Contracts\EnvironmentAwareApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Environment\CompositeEnvironmentProvider;
use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\DotenvEnvironmentProvider;
use Sif\Foundation\Environment\EnvironmentRepository;
use Sif\Foundation\Environment\NativeEnvironmentProvider;
use Sif\Foundation\Modules\Runtime\ModuleRuntimeBootstrapper;
use Sif\Foundation\Logging\Orchestration\StructuredLogger;
use Sif\Foundation\Logging\Planning\LoggingPlan;
use Sif\Foundation\Logging\Runtime\RuntimeLoggingServiceProvider;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandler;
use Sif\Foundation\ErrorHandling\Planning\ErrorHandlingPlan;
use Sif\Foundation\ErrorHandling\Runtime\RuntimeErrorHandlingServiceProvider;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;
use Sif\Foundation\Resources\Runtime\RuntimeResourceManagementServiceProvider;
use Sif\Foundation\Installer\Runtime\InstallerRuntime;
use Sif\Foundation\Installer\Runtime\RuntimeInstallerServiceProvider;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;
use Sif\Foundation\Migration\Runtime\RuntimeMigrationServiceProvider;
use Sif\Foundation\Persistence\Pdo\Runtime\PdoPersistenceRuntime;
use Sif\Foundation\Persistence\Pdo\Runtime\PdoPersistenceRuntimeServiceProvider;
use Sif\Foundation\Model\Runtime\BaseModelRuntime;
use Sif\Foundation\Model\Runtime\BaseModelRuntimeServiceProvider;
use Sif\Foundation\Cli\Runtime\CliRuntime;
use Sif\Foundation\Cli\Runtime\CliRuntimeServiceProvider;

final class Bootstrap implements BootstrapInterface
{
    private ConfigurationFileLoader $configurationLoader;

    /** @var list<string> */
    private array $configurationSources;

    private EnvironmentProviderInterface $nativeEnvironment;

    private ?string $dotenvSource;

    private ?ConfigurationBootstrapper $configurationBootstrapper;

    private ?ModuleRuntimeBootstrapper $moduleRuntimeBootstrapper;

    private ?LoggingPlan $loggingPlan;

    private ?ErrorHandlingPlan $errorHandlingPlan;

    private ?ResourceManagementPlan $resourceManagementPlan;

    private ?InstallerRuntime $installer;

    private ?MigrationRuntime $migrations;

    private ?PdoPersistenceRuntime $persistence;

    private ?BaseModelRuntime $models;

    private ?CliRuntime $cli;

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
        ?ModuleRuntimeBootstrapper $moduleRuntimeBootstrapper = null,
        ?LoggingPlan $loggingPlan = null,
        ?ErrorHandlingPlan $errorHandlingPlan = null,
        ?ResourceManagementPlan $resourceManagementPlan = null,
        ?InstallerRuntime $installer = null,
        ?MigrationRuntime $migrations = null,
        ?PdoPersistenceRuntime $persistence = null,
        ?BaseModelRuntime $models = null,
        ?CliRuntime $cli = null,
    ) {
        $this->configurationLoader = $configurationLoader
            ?? ConfigurationFileLoader::withDefaultLoaders();
        $this->configurationSources = [];
        $this->nativeEnvironment = $nativeEnvironment ?? new NativeEnvironmentProvider();
        $this->dotenvSource = $dotenvSource;
        $this->configurationBootstrapper = $configurationBootstrapper;
        $this->moduleRuntimeBootstrapper = $moduleRuntimeBootstrapper;
        $this->loggingPlan = $loggingPlan;
        $this->errorHandlingPlan = $errorHandlingPlan;
        $this->resourceManagementPlan = $resourceManagementPlan;
        $this->installer = $installer;
        $this->migrations = $migrations;
        $this->persistence = $persistence;
        $this->models = $models;
        $this->cli = $cli;

        foreach ($configurationSources as $source) {
            $this->configurationSources[] = $source;
        }
    }
    public function createApplication(EnvironmentInterface $environment): Application
    {
        $lifecycle = new Lifecycle();
        $providers = new ServiceProviderCollection();
        $logger = $this->loggingPlan !== null ? new StructuredLogger($this->loggingPlan) : null;
        if ($logger !== null) {
            $providers->add(new RuntimeLoggingServiceProvider($logger));
        }
        $errorHandler = $this->errorHandlingPlan !== null
            ? new ErrorHandler($this->errorHandlingPlan)
            : null;
        if ($errorHandler !== null) {
            $providers->add(new RuntimeErrorHandlingServiceProvider($errorHandler));
        }
        if ($this->resourceManagementPlan !== null) {
            $providers->add(new RuntimeResourceManagementServiceProvider(
                $this->resourceManagementPlan,
            ));
        }
        if ($this->installer !== null) {
            $providers->add(new RuntimeInstallerServiceProvider($this->installer));
        }
        if ($this->migrations !== null) {
            $providers->add(new RuntimeMigrationServiceProvider($this->migrations));
        }
        if ($this->persistence !== null) {
            $providers->add(new PdoPersistenceRuntimeServiceProvider($this->persistence));
        }
        if ($this->models !== null) {
            $providers->add(new BaseModelRuntimeServiceProvider($this->models));
        }
        if ($this->cli !== null) {
            $providers->add(new CliRuntimeServiceProvider($this->cli));
        }
        $kernel = new Kernel($lifecycle);
        $variables = $this->createEnvironmentRepository();
        $runtime = new Runtime($variables);
        $configurationValues = $this->configurationBootstrapper !== null
            ? $this->configurationBootstrapper->load()->snapshot()->values()
            : $this->configurationLoader->loadMany($this->configurationSources);
        $configuration = new ConfigurationRepository($configurationValues);
        $capabilities = new CapabilityRegistry();
        foreach (['runtime', 'foundation', 'providers', 'lifecycle', 'configuration'] as $identifier) {
            $capabilities->register(new NamedCapability($identifier));
        }
        $serviceDefinitions = new ServiceDefinitionRegistry();
        $moduleRuntime = $this->moduleRuntimeBootstrapper?->integrate(
            $configuration,
            $serviceDefinitions,
            $capabilities,
            $providers,
        );

        return new Application(
            $runtime,
            $kernel,
            $environment,
            $providers,
            $capabilities,
            $configuration,
            $variables,
            $serviceDefinitions,
            $moduleRuntime,
            $logger,
            $errorHandler,
            $this->resourceManagementPlan,
            $this->resourceManagementPlan?->createPathResolver(),
            $this->installer,
            $this->migrations,
            $this->persistence,
            $this->models,
            $this->cli,
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
