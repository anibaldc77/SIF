<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\Contracts\MutableConfigurationInterface;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Contracts\EnvironmentAwareApplicationInterface;
use Sif\Foundation\Contracts\MutableLoggingApplicationInterface;
use Sif\Foundation\Contracts\MutableInstallerApplicationInterface;
use Sif\Foundation\Contracts\MutableMigrationApplicationInterface;
use Sif\Foundation\Contracts\MutablePersistenceApplicationInterface;
use Sif\Foundation\Contracts\MutableBaseModelApplicationInterface;
use Sif\Foundation\Contracts\MutableCliApplicationInterface;
use Sif\Foundation\Contracts\MutableApplicationSkeletonApplicationInterface;
use Sif\Foundation\Contracts\MutableHttpApplicationInterface;
use Sif\Foundation\Contracts\MutableErrorHandlingApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Environment\Contracts\MutableEnvironmentInterface;
use Sif\Foundation\Environment\EnvironmentRepository;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Contracts\RuntimeInterface;
use Sif\Foundation\Exceptions\InvalidCapabilityException;
use Sif\Foundation\Modules\Runtime\ModuleRuntimeIntegrationResult;
use Sif\Foundation\Logging\Contracts\LoggerInterface;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandlingResult;
use Sif\Foundation\Resources\Contracts\ResourcePathResolverInterface;
use Sif\Foundation\Resources\Planning\ResourceManagementPlan;
use Sif\Foundation\Installer\Runtime\InstallerRuntime;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;
use Sif\Foundation\Persistence\Pdo\Runtime\PdoPersistenceRuntime;
use Sif\Foundation\Model\Runtime\BaseModelRuntime;
use Sif\Foundation\Cli\Runtime\CliRuntime;
use Sif\Foundation\ApplicationSkeleton\Runtime\ApplicationSkeletonRuntime;
use Sif\Foundation\Http\Runtime\HttpRuntime;

/** Owns the isolated runtime graph and its ordered provider collection. */
final class Application implements EnvironmentAwareApplicationInterface, MutableLoggingApplicationInterface, MutableErrorHandlingApplicationInterface, \Sif\Foundation\Contracts\MutableResourceManagementApplicationInterface, MutableInstallerApplicationInterface, MutableMigrationApplicationInterface, MutablePersistenceApplicationInterface, MutableBaseModelApplicationInterface, MutableCliApplicationInterface, MutableApplicationSkeletonApplicationInterface, MutableHttpApplicationInterface
{
    private CapabilityRegistry $capabilityRegistry;

    private MutableConfigurationInterface $configuration;

    private MutableEnvironmentInterface $variables;

    private ServiceDefinitionRegistry $serviceDefinitions;

    private ?ModuleRuntimeIntegrationResult $moduleRuntime;

    private ?LoggerInterface $logger;

    private ?ErrorHandlerInterface $errorHandler;

    private ?ErrorHandlingResult $lastErrorHandlingResult = null;

    private ?ResourceManagementPlan $resourceManagementPlan;

    private ?ResourcePathResolverInterface $resourcePathResolver;

    private ?InstallerRuntime $installer;

    private ?MigrationRuntime $migrations;

    private ?PdoPersistenceRuntime $persistence;

    private ?BaseModelRuntime $models;

    private ?CliRuntime $cli;

    private ?ApplicationSkeletonRuntime $applicationSkeleton;

    private ?HttpRuntime $http;

    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly KernelInterface $kernel,
        private readonly EnvironmentInterface $environment,
        private readonly ServiceProviderCollection $providers,
        ?CapabilityRegistry $capabilityRegistry = null,
        ?MutableConfigurationInterface $configuration = null,
        ?MutableEnvironmentInterface $variables = null,
        ?ServiceDefinitionRegistry $serviceDefinitions = null,
        ?ModuleRuntimeIntegrationResult $moduleRuntime = null,
        ?LoggerInterface $logger = null,
        ?ErrorHandlerInterface $errorHandler = null,
        ?ResourceManagementPlan $resourceManagementPlan = null,
        ?ResourcePathResolverInterface $resourcePathResolver = null,
        ?InstallerRuntime $installer = null,
        ?MigrationRuntime $migrations = null,
        ?PdoPersistenceRuntime $persistence = null,
        ?BaseModelRuntime $models = null,
        ?CliRuntime $cli = null,
        ?ApplicationSkeletonRuntime $applicationSkeleton = null,
        ?HttpRuntime $http = null,
    ) {
        $this->configuration = $configuration ?? new ConfigurationRepository();
        $this->variables = $variables ?? new EnvironmentRepository();
        $this->capabilityRegistry = $capabilityRegistry ?? new CapabilityRegistry();
        $this->serviceDefinitions = $serviceDefinitions ?? new ServiceDefinitionRegistry();
        $this->moduleRuntime = $moduleRuntime;
        $this->logger = $logger;
        $this->errorHandler = $errorHandler;
        $this->resourceManagementPlan = $resourceManagementPlan;
        $this->resourcePathResolver = $resourcePathResolver;
        $this->installer = $installer;
        $this->migrations = $migrations;
        $this->persistence = $persistence;
        $this->models = $models;
        $this->cli = $cli;
        $this->applicationSkeleton = $applicationSkeleton;
        $this->http = $http;

        foreach (['runtime', 'foundation', 'providers', 'lifecycle', 'configuration'] as $identifier) {
            if (!$this->capabilityRegistry->has($identifier)) {
                $this->capabilityRegistry->register(new NamedCapability($identifier));
            }
        }
    }

    public function runtime(): RuntimeInterface
    {
        return $this->runtime;
    }

    public function kernel(): KernelInterface
    {
        return $this->kernel;
    }

    public function environment(): EnvironmentInterface
    {
        return $this->environment;
    }

    public function providers(): ServiceProviderCollection
    {
        return $this->providers;
    }

    /** @return list<string> */
    public function capabilities(): array
    {
        return array_map(
            static fn (CapabilityInterface $capability): string => $capability->identifier(),
            $this->capabilityRegistry->all(),
        );
    }

    public function hasCapability(string $capability): bool
    {
        return $this->capabilityRegistry->has($this->normalizeCapability($capability));
    }

    public function addCapability(string $capability): void
    {
        $identifier = $this->normalizeCapability($capability);

        if (!$this->capabilityRegistry->has($identifier)) {
            $this->capabilityRegistry->register(new NamedCapability($identifier));
        }
    }

    public function capabilityRegistry(): CapabilityRegistry
    {
        return $this->capabilityRegistry;
    }


    public function serviceDefinitions(): ServiceDefinitionRegistry
    {
        return $this->serviceDefinitions;
    }

    public function moduleRuntime(): ?ModuleRuntimeIntegrationResult
    {
        return $this->moduleRuntime;
    }

    public function logger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function errorHandler(): ?ErrorHandlerInterface
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    public function lastErrorHandlingResult(): ?ErrorHandlingResult
    {
        return $this->lastErrorHandlingResult;
    }

    public function resourceManagementPlan(): ?ResourceManagementPlan
    {
        return $this->resourceManagementPlan;
    }

    public function resourcePathResolver(): ?ResourcePathResolverInterface
    {
        return $this->resourcePathResolver;
    }

    public function setResourceManagement(
        ResourceManagementPlan $plan,
        ResourcePathResolverInterface $resolver,
    ): void {
        $this->resourceManagementPlan = $plan;
        $this->resourcePathResolver = $resolver;
    }

    public function installer(): ?InstallerRuntime
    {
        return $this->installer;
    }

    public function setInstaller(InstallerRuntime $installer): void
    {
        $this->installer = $installer;
    }

    public function migrations(): ?MigrationRuntime
    {
        return $this->migrations;
    }

    public function setMigrations(MigrationRuntime $migrations): void
    {
        $this->migrations = $migrations;
    }

    public function persistence(): ?PdoPersistenceRuntime
    {
        return $this->persistence;
    }

    public function setPersistence(PdoPersistenceRuntime $persistence): void
    {
        $this->persistence = $persistence;
    }

    public function models(): ?BaseModelRuntime
    {
        return $this->models;
    }

    public function setModels(BaseModelRuntime $models): void
    {
        $this->models = $models;
    }

    public function cli(): ?CliRuntime
    {
        return $this->cli;
    }

    public function setCli(CliRuntime $cli): void
    {
        $this->cli = $cli;
    }

    public function applicationSkeleton(): ?ApplicationSkeletonRuntime
    {
        return $this->applicationSkeleton;
    }

    public function setApplicationSkeleton(ApplicationSkeletonRuntime $applicationSkeleton): void
    {
        $this->applicationSkeleton = $applicationSkeleton;
    }

    public function http(): ?HttpRuntime
    {
        return $this->http;
    }

    public function setHttp(HttpRuntime $http): void
    {
        $this->http = $http;
    }

    public function configuration(): MutableConfigurationInterface
    {
        return $this->configuration;
    }

    public function variables(): MutableEnvironmentInterface
    {
        return $this->variables;
    }

    public function registerCapability(CapabilityInterface $capability): void
    {
        $identifier = $this->normalizeCapability($capability->identifier());

        if ($identifier !== $capability->identifier()) {
            throw InvalidCapabilityException::invalid($capability->identifier());
        }

        $this->capabilityRegistry->register($capability);
    }

    public function capability(string $identifier): CapabilityInterface
    {
        return $this->capabilityRegistry->get($this->normalizeCapability($identifier));
    }

    public function boot(): BootResult
    {
        return $this->observeLifecycleResult($this->kernel->boot($this), 'runtime.boot');
    }

    public function run(): BootResult
    {
        return $this->observeLifecycleResult($this->kernel->run($this), 'runtime.run');
    }

    public function shutdown(): BootResult
    {
        return $this->observeLifecycleResult($this->kernel->shutdown($this), 'runtime.shutdown');
    }

    private function observeLifecycleResult(BootResult $result, string $origin): BootResult
    {
        $cause = $result->cause();
        if ($result->succeeded() || $cause === null || $this->errorHandler === null) {
            return $result;
        }

        try {
            $this->lastErrorHandlingResult = $this->errorHandler->handle(
                $cause,
                new FailureOrigin($origin),
                [
                    'boot_stage' => $result->stage()->value,
                    'error_count' => count($result->errors()),
                    'runtime_state' => $this->runtime->state()->value,
                ],
            );
        } catch (\Throwable) {
            // Terminal observation boundary: preserve the original BootResult and cause.
        }

        return $result;
    }

    private function normalizeCapability(string $capability): string
    {
        $capability = strtolower(trim($capability));

        if ($capability === '') {
            throw InvalidCapabilityException::empty();
        }

        if (str_contains($capability, ' ')) {
            throw InvalidCapabilityException::invalid($capability);
        }

        if (!preg_match('/^[a-z0-9_-]+(?:\.[a-z0-9_-]+)*$/D', $capability)) {
            throw InvalidCapabilityException::invalid($capability);
        }

        return $capability;
    }
}
