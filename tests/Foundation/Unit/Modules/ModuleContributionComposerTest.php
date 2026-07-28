<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Configuration\Source\ArrayConfigurationSource;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\ServiceProviderInterface;
use Sif\Foundation\Modules\Contribution\ModuleConfigurationNamespace;
use Sif\Foundation\Modules\Contribution\ModuleContributionSet;
use Sif\Foundation\Modules\Contracts\ModuleContributionProviderInterface;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Exceptions\InvalidModuleCompositionException;
use Sif\Foundation\Modules\Exceptions\ModuleContributionCollisionException;
use Sif\Foundation\Modules\ModuleContributionComposer;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleRegistry;
use Sif\Foundation\Modules\ModuleVersion;
use Sif\Foundation\Modules\Planning\ResolvedModulePlan;

final class ModuleContributionComposerTest extends TestCase
{
    public function testComposesContributionsInResolvedOrderAndPublishesOwnership(): void
    {
        $first = $this->module('first', 'first.config', 'first.service', 'first.capability', FirstProvider::class);
        $second = $this->module('second', 'second.config', 'second.service', 'second.capability', SecondProvider::class);
        $registry = $this->registry($first, $second);
        $plan = new ResolvedModulePlan([$second->descriptor(), $first->descriptor()], [], []);

        $result = (new ModuleContributionComposer())->compose($plan, $registry);

        self::assertSame(['second.source', 'first.source'], array_map(static fn ($source): string => $source->id(), $result->configurationSources()));
        self::assertSame(['second.service', 'first.service'], array_map(static fn ($definition): string => $definition->identifier()->value(), $result->serviceDefinitions()));
        self::assertSame(['second.capability', 'first.capability'], array_map(static fn ($capability): string => $capability->identifier(), $result->capabilities()));
        self::assertSame([SecondProvider::class, FirstProvider::class], $result->serviceProviders());
        self::assertSame('second', $result->serviceDefinitionOwners()['second.service']);
    }

    public function testModuleWithoutContributionProviderIsAccepted(): void
    {
        $module = new class implements ModuleInterface {
            public function descriptor(): ModuleDescriptor
            {
                return new ModuleDescriptor(new ModuleId('plain'), new ModuleVersion('1.0.0'), 'Plain');
            }
        };
        $registry = $this->registry($module);

        $result = (new ModuleContributionComposer())->compose(new ResolvedModulePlan([$module->descriptor()], [], []), $registry);

        self::assertSame([], $result->serviceDefinitions());
    }

    public function testDuplicateConfigurationNamespaceIsRejected(): void
    {
        $first = $this->module('first', 'shared.config', 'first.service', 'first.capability', FirstProvider::class);
        $second = $this->module('second', 'shared.config', 'second.service', 'second.capability', SecondProvider::class);

        $this->expectException(ModuleContributionCollisionException::class);
        (new ModuleContributionComposer())->compose(
            new ResolvedModulePlan([$first->descriptor(), $second->descriptor()], [], []),
            $this->registry($first, $second),
        );
    }

    public function testDuplicateServiceDefinitionIsRejected(): void
    {
        $first = $this->module('first', 'first.config', 'shared.service', 'first.capability', FirstProvider::class);
        $second = $this->module('second', 'second.config', 'shared.service', 'second.capability', SecondProvider::class);

        $this->expectException(ModuleContributionCollisionException::class);
        (new ModuleContributionComposer())->compose(new ResolvedModulePlan([$first->descriptor(), $second->descriptor()], [], []), $this->registry($first, $second));
    }

    public function testDuplicateCapabilityIsRejected(): void
    {
        $first = $this->module('first', 'first.config', 'first.service', 'shared.capability', FirstProvider::class);
        $second = $this->module('second', 'second.config', 'second.service', 'shared.capability', SecondProvider::class);

        $this->expectException(ModuleContributionCollisionException::class);
        (new ModuleContributionComposer())->compose(new ResolvedModulePlan([$first->descriptor(), $second->descriptor()], [], []), $this->registry($first, $second));
    }

    public function testDescriptorAndContributionNamespaceMustMatch(): void
    {
        $module = $this->module('module', 'module.config', 'module.service', 'module.capability', FirstProvider::class, 'different.config');

        $this->expectException(InvalidModuleCompositionException::class);
        (new ModuleContributionComposer())->compose(new ResolvedModulePlan([$module->descriptor()], [], []), $this->registry($module));
    }

    public function testUndeclaredCapabilityIsRejected(): void
    {
        $module = $this->module('module', 'module.config', 'module.service', 'actual.capability', FirstProvider::class, null, 'declared.capability');

        $this->expectException(InvalidModuleCompositionException::class);
        (new ModuleContributionComposer())->compose(new ResolvedModulePlan([$module->descriptor()], [], []), $this->registry($module));
    }

    public function testInvalidServiceProviderClassIsRejected(): void
    {
        $module = $this->module('module', 'module.config', 'module.service', 'module.capability', \stdClass::class);

        $this->expectException(InvalidModuleCompositionException::class);
        (new ModuleContributionComposer())->compose(new ResolvedModulePlan([$module->descriptor()], [], []), $this->registry($module));
    }

    /** @param class-string $provider */
    private function module(
        string $id,
        string $namespace,
        string $service,
        string $capability,
        string $provider,
        ?string $contributionNamespace = null,
        ?string $declaredCapability = null,
    ): ModuleInterface {
        return new class ($id, $namespace, $service, $capability, $provider, $contributionNamespace, $declaredCapability) implements ModuleInterface, ModuleContributionProviderInterface {
            /** @param class-string $provider */
            public function __construct(
                private string $id,
                private string $namespace,
                private string $service,
                private string $capability,
                private string $provider,
                private ?string $contributionNamespace,
                private ?string $declaredCapability,
            ) {
            }

            public function descriptor(): ModuleDescriptor
            {
                $declaredCapability = $this->declaredCapability ?? $this->capability;
                if ($declaredCapability === '') {
                    throw new \LogicException('Capability must not be empty.');
                }

                return new ModuleDescriptor(
                    new ModuleId($this->id),
                    new ModuleVersion('1.0.0'),
                    ucfirst($this->id),
                    providedCapabilities: [$declaredCapability],
                    configurationNamespace: $this->namespace,
                    serviceProviders: [$this->provider],
                );
            }

            public function contributions(): ModuleContributionSet
            {
                return new ModuleContributionSet(
                    new ModuleConfigurationNamespace($this->contributionNamespace ?? $this->namespace),
                    [new ArrayConfigurationSource($this->id . '.source', [])],
                    [ServiceDefinition::forInstance(new ServiceIdentifier($this->service), new \stdClass())],
                    [new NamedCapability($this->capability)],
                );
            }
        };
    }

    private function registry(ModuleInterface ...$modules): ModuleRegistry
    {
        $registry = new ModuleRegistry();
        foreach ($modules as $module) {
            $registry->register($module);
        }
        return $registry;
    }
}

final class FirstProvider implements ServiceProviderInterface
{
    public function register(ApplicationInterface $application): void {}
    public function boot(ApplicationInterface $application): void {}
    public function shutdown(ApplicationInterface $application): void {}
}

final class SecondProvider implements ServiceProviderInterface
{
    public function register(ApplicationInterface $application): void {}
    public function boot(ApplicationInterface $application): void {}
    public function shutdown(ApplicationInterface $application): void {}
}
