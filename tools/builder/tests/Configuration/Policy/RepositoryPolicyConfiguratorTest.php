<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Policy;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\Policy\RepositoryPolicyConfigurator;
use Sif\Builder\Configuration\RepositoryConfiguration;

final class RepositoryPolicyConfiguratorTest extends TestCase
{
    public function testBuildsPolicySetFromDeclarativeConfiguration(): void
    {
        $configuration = new RepositoryConfiguration(
            schemaVersion: '1.0',
            defaultProfile: 'default',
            profiles: ['default' => []],
            repositoryPolicies: [
                'required.category' => [[
                    'id' => 'repository.architecture',
                    'category' => 'architecture',
                    'severity' => 'warning',
                ]],
                'required.metadata' => [[
                    'id' => 'repository.approval',
                    'field' => 'approved_by',
                    'status' => 'approved',
                ]],
            ],
        );

        $result = RepositoryPolicyConfigurator::withBuiltInFactories()->configure($configuration);

        self::assertTrue($result->isSuccessful());
        self::assertNotNull($result->policies);
        self::assertSame(
            ['repository.approval', 'repository.architecture'],
            array_map(static fn ($rule): string => $rule->id(), $result->policies->all()),
        );
    }

    public function testReturnsAllInvalidPolicyDiagnosticsInDeterministicInputOrder(): void
    {
        $configuration = new RepositoryConfiguration(
            schemaVersion: '1.0',
            defaultProfile: 'default',
            profiles: ['default' => []],
            repositoryPolicies: [
                'unknown.policy' => [['id' => 'repository.unknown']],
                'required.metadata' => [
                    ['id' => 'repository.invalid', 'field' => '', 'unexpected' => true],
                    ['id' => 'repository.invalid-severity', 'field' => 'owner', 'severity' => 'critical'],
                ],
            ],
            sourcePath: 'D:/repository/.sif/builder.json',
        );

        $result = RepositoryPolicyConfigurator::withBuiltInFactories()->configure($configuration);

        self::assertFalse($result->isSuccessful());
        self::assertNull($result->policies);
        self::assertSame(['CONFIG-112', 'CONFIG-112', 'CONFIG-112'], array_map(
            static fn ($diagnostic): string => $diagnostic->code,
            $result->diagnostics,
        ));
        self::assertSame('D:/repository/.sif/builder.json', $result->diagnostics[0]->path);
    }

    public function testRejectsDuplicateRuleIdentifiersAcrossFactories(): void
    {
        $configuration = new RepositoryConfiguration(
            schemaVersion: '1.0',
            defaultProfile: 'default',
            profiles: ['default' => []],
            repositoryPolicies: [
                'required.category' => [['id' => 'repository.required', 'category' => 'architecture']],
                'required.metadata' => [['id' => 'repository.required', 'field' => 'owner']],
            ],
        );

        $result = RepositoryPolicyConfigurator::withBuiltInFactories()->configure($configuration);

        self::assertFalse($result->isSuccessful());
        self::assertSame('CONFIG-112', $result->diagnostics[0]->code);
    }

    public function testEmptyConfigurationProducesEmptyPolicySet(): void
    {
        $result = RepositoryPolicyConfigurator::withBuiltInFactories()->configure(RepositoryConfiguration::builtInDefault());

        self::assertTrue($result->isSuccessful());
        self::assertTrue($result->policies?->isEmpty());
    }
}
