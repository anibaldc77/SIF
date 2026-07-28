<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Enablement\ExplicitModuleEnablementPolicy;
use Sif\Foundation\Modules\Enablement\ModuleEnablementDecision;
use Sif\Foundation\Modules\Exceptions\DisabledRequiredModuleException;
use Sif\Foundation\Modules\Exceptions\FrozenModuleRegistryException;
use Sif\Foundation\Modules\ModuleDependency;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModulePlanResolver;
use Sif\Foundation\Modules\ModuleRegistry;
use Sif\Foundation\Modules\ModuleVersion;

final class ModulePlanResolverTest extends TestCase
{
    public function testPolicyDisablesUnlistedModulesByDefault(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('alpha'));

        $plan = (new ModulePlanResolver())->resolve($registry, new ExplicitModuleEnablementPolicy([]));

        self::assertSame([], $plan->enabledModules());
        self::assertSame('alpha', $plan->disabledModules()[0]->descriptor()->id()->value());
        self::assertSame('MODULE_NOT_EXPLICITLY_ENABLED', $plan->disabledModules()[0]->reasonCode());
    }

    public function testExplicitEnabledModulesAreResolvedInDependencyOrder(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('app', [new ModuleDependency(new ModuleId('core'))]));
        $registry->register($this->module('core'));

        $policy = new ExplicitModuleEnablementPolicy([
            'app' => ModuleEnablementDecision::enabled(),
            'core' => ModuleEnablementDecision::enabled(),
        ]);

        $plan = (new ModulePlanResolver())->resolve($registry, $policy);

        self::assertSame(['core', 'app'], $this->ids($plan->enabledModules()));
        self::assertSame(['core'], $plan->dependenciesByModule()['app']);
        self::assertSame(['app', 'core'], $this->ids($plan->shutdownOrder()));
    }

    public function testDisabledModulesPreserveRegistrationOrderAndSafeReasons(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('alpha'));
        $registry->register($this->module('beta'));

        $policy = new ExplicitModuleEnablementPolicy([
            'alpha' => ModuleEnablementDecision::disabled('PROFILE_DISABLED'),
            'beta' => ModuleEnablementDecision::disabled('APPLICATION_DISABLED'),
        ]);

        $plan = (new ModulePlanResolver())->resolve($registry, $policy);

        self::assertSame(['alpha', 'beta'], array_map(
            static fn ($disabled): string => $disabled->descriptor()->id()->value(),
            $plan->disabledModules(),
        ));
        self::assertSame(['PROFILE_DISABLED', 'APPLICATION_DISABLED'], array_map(
            static fn ($disabled): string => $disabled->reasonCode(),
            $plan->disabledModules(),
        ));
    }

    public function testDisabledRequiredDependencyFailsBeforePlanPublication(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('app', [new ModuleDependency(new ModuleId('core'))]));
        $registry->register($this->module('core'));

        $policy = new ExplicitModuleEnablementPolicy([
            'app' => ModuleEnablementDecision::enabled(),
            'core' => ModuleEnablementDecision::disabled('PROFILE_DISABLED'),
        ]);

        try {
            (new ModulePlanResolver())->resolve($registry, $policy);
            self::fail('Expected disabled dependency failure.');
        } catch (DisabledRequiredModuleException $exception) {
            self::assertSame('Enabled module "app" requires disabled module "core".', $exception->getMessage());
            self::assertFalse($registry->isFrozen());
        }
    }

    public function testDisabledOptionalDependencyIsIgnored(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('app', optional: [new ModuleDependency(new ModuleId('addon'))]));
        $registry->register($this->module('addon'));

        $policy = new ExplicitModuleEnablementPolicy([
            'app' => ModuleEnablementDecision::enabled(),
            'addon' => ModuleEnablementDecision::disabled('PROFILE_DISABLED'),
        ]);

        $plan = (new ModulePlanResolver())->resolve($registry, $policy);

        self::assertSame(['app'], $this->ids($plan->enabledModules()));
        self::assertSame([], $plan->dependenciesByModule()['app']);
    }

    public function testSuccessfulResolutionFreezesSourceRegistry(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('alpha'));

        (new ModulePlanResolver())->resolve($registry, new ExplicitModuleEnablementPolicy([
            'alpha' => ModuleEnablementDecision::enabled(),
        ]));

        self::assertTrue($registry->isFrozen());
        $this->expectException(FrozenModuleRegistryException::class);
        $registry->register($this->module('beta'));
    }

    public function testDefaultDecisionCanEnableAllRegisteredModules(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('alpha'));
        $registry->register($this->module('beta'));

        $policy = new ExplicitModuleEnablementPolicy([], ModuleEnablementDecision::enabled());
        $plan = (new ModulePlanResolver())->resolve($registry, $policy);

        self::assertSame(['alpha', 'beta'], $this->ids($plan->enabledModules()));
        self::assertSame([], $plan->disabledModules());
    }

    public function testDecisionInvariantsRejectUnsafeStates(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ModuleEnablementDecision::disabled('');
    }

    /**
     * @param list<ModuleDependency> $required
     * @param list<ModuleDependency> $optional
     */
    private function module(string $id, array $required = [], array $optional = []): ModuleInterface
    {
        return new class ($id, $required, $optional) implements ModuleInterface {
            private ModuleDescriptor $descriptor;

            /**
             * @param list<ModuleDependency> $required
             * @param list<ModuleDependency> $optional
             */
            public function __construct(string $id, array $required, array $optional)
            {
                $this->descriptor = new ModuleDescriptor(
                    new ModuleId($id),
                    new ModuleVersion('1.0.0'),
                    ucfirst($id),
                    requiredDependencies: $required,
                    optionalDependencies: $optional,
                );
            }

            public function descriptor(): ModuleDescriptor
            {
                return $this->descriptor;
            }
        };
    }

    /**
     * @param list<ModuleDescriptor> $descriptors
     * @return list<string>
     */
    private function ids(array $descriptors): array
    {
        return array_map(static fn (ModuleDescriptor $descriptor): string => $descriptor->id()->value(), $descriptors);
    }
}
