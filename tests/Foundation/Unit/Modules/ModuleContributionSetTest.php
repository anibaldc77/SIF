<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Configuration\Source\ArrayConfigurationSource;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Modules\Contribution\ModuleConfigurationNamespace;
use Sif\Foundation\Modules\Contribution\ModuleContributionSet;
use Sif\Foundation\Modules\Exceptions\InvalidModuleContributionException;

final class ModuleContributionSetTest extends TestCase
{
    public function testValidContributionSetPreservesDeclaredOrder(): void
    {
        $source = new ArrayConfigurationSource('module.defaults', ['enabled' => true]);
        $definition = ServiceDefinition::forInstance(new ServiceIdentifier('module.service'), new \stdClass());
        $capability = new NamedCapability('module.capability');

        $set = new ModuleContributionSet(
            new ModuleConfigurationNamespace('module.feature'),
            [$source],
            [$definition],
            [$capability],
        );

        self::assertSame('module.feature', $set->configurationNamespace()?->value());
        self::assertSame([$source], $set->configurationSources());
        self::assertSame([$definition], $set->serviceDefinitions());
        self::assertSame([$capability], $set->capabilities());
    }

    public function testConfigurationSourcesRequireExplicitNamespace(): void
    {
        $this->expectException(InvalidModuleContributionException::class);
        new ModuleContributionSet(configurationSources: [new ArrayConfigurationSource('defaults', [])]);
    }

    public function testInvalidConfigurationNamespaceIsRejected(): void
    {
        $this->expectException(InvalidModuleContributionException::class);
        new ModuleConfigurationNamespace('Invalid Namespace');
    }

    public function testDuplicateConfigurationSourceIdentifiersAreRejected(): void
    {
        $this->expectException(InvalidModuleContributionException::class);
        new ModuleContributionSet(
            new ModuleConfigurationNamespace('module'),
            [new ArrayConfigurationSource('defaults', []), new ArrayConfigurationSource('defaults', [])],
        );
    }

    public function testDuplicateServiceDefinitionIdentifiersAreRejected(): void
    {
        $this->expectException(InvalidModuleContributionException::class);
        new ModuleContributionSet(serviceDefinitions: [
            ServiceDefinition::forInstance(new ServiceIdentifier('service'), new \stdClass()),
            ServiceDefinition::forInstance(new ServiceIdentifier('service'), new \stdClass()),
        ]);
    }

    public function testDuplicateCapabilityIdentifiersAreRejected(): void
    {
        $this->expectException(InvalidModuleContributionException::class);
        new ModuleContributionSet(capabilities: [
            new NamedCapability('capability'),
            new NamedCapability('capability'),
        ]);
    }

    public function testEmptyContributionSetIsValid(): void
    {
        $set = new ModuleContributionSet();

        self::assertNull($set->configurationNamespace());
        self::assertSame([], $set->configurationSources());
        self::assertSame([], $set->serviceDefinitions());
        self::assertSame([], $set->capabilities());
    }
}
