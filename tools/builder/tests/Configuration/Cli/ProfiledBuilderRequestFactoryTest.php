<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Configuration\BuilderRequestFactory;
use Sif\Builder\Cli\Configuration\WorkingDirectoryPathResolver;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Configuration\Cli\CliRepositoryConfigurationResolver;
use Sif\Builder\Configuration\Cli\ProfiledBuilderRequestFactory;
use Sif\Builder\Configuration\Cli\ResolvedCliConfigurationStore;

final class ProfiledBuilderRequestFactoryTest extends TestCase
{
    public function testAppliesSelectedProfileAndPreservesExplicitCliOverrides(): void
    {
        $root = sys_get_temp_dir() . '/sif-builder-profile-' . bin2hex(random_bytes(4));
        mkdir($root . '/.sif', 0777, true);
        file_put_contents($root . '/.sif/builder.json', json_encode([
            'schema_version' => '1.0',
            'default_profile' => 'development',
            'repository_policies' => [
                'required.category' => [[
                    'id' => 'repository.architecture',
                    'category' => 'architecture',
                    'severity' => 'warning',
                ]],
            ],
            'profiles' => [
                'development' => [
                    'analyzers' => ['metadata.completeness'],
                    'generators' => ['repository.index'],
                    'reporters' => ['report.json'],
                    'execution' => ['strict' => false],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $paths = new WorkingDirectoryPathResolver($root);
        $store = new ResolvedCliConfigurationStore();
        $factory = new ProfiledBuilderRequestFactory(
            new BuilderRequestFactory($paths),
            new CliRepositoryConfigurationResolver($paths),
            $store,
        );

        $request = $factory->create(new CommandInput('build', options: [
            'repository' => ['.'],
            'analyzer' => ['reference.integrity'],
        ]), EngineExecutionMode::BUILD);

        self::assertSame(['reference.integrity'], $request->enabledAnalyzers);
        self::assertSame(['repository.index'], $request->enabledGenerators);
        self::assertSame('development', $store->current()->profile->identifier);
$policyIdentifiers = array_map(
    static fn ($policy): string => $policy->id(),
    $store->current()->policies->all(),
);

self::assertSame(
    ['repository.architecture'],
    $policyIdentifiers,
);
    }
}
