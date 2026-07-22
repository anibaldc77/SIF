<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Profile;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\Profile\BuildProfileResolver;
use Sif\Builder\Configuration\RepositoryConfiguration;

final class BuildProfileResolverTest extends TestCase
{
    public function testResolvesTheDefaultProfileWithoutInheritance(): void
    {
        $result = (new BuildProfileResolver())->resolve(RepositoryConfiguration::builtInDefault());

        self::assertTrue($result->isSuccessful());
        self::assertSame('default', $result->profile?->identifier);
        self::assertCount(5, $result->profile?->analyzers ?? []);
        self::assertFalse($result->profile?->strict);
    }

    public function testResolvesSingleInheritanceUsingReplacementSemantics(): void
    {
        $configuration = $this->configuration([
            'base' => [
                'analyzers' => ['metadata.completeness'],
                'generators' => ['repository.index'],
                'reporters' => ['report.markdown'],
                'execution' => ['strict' => false],
            ],
            'release' => [
                'extends' => 'base',
                'generators' => ['repository.manifest'],
                'execution' => ['strict' => true],
            ],
        ], 'release');

        $result = (new BuildProfileResolver())->resolve($configuration);

        self::assertTrue($result->isSuccessful());
        self::assertSame(['metadata.completeness'], $result->profile?->analyzers);
        self::assertSame(['repository.manifest'], $result->profile?->generators);
        self::assertSame(['report.markdown'], $result->profile?->reporters);
        self::assertTrue($result->profile?->strict);
    }

    public function testAnExplicitEmptyListDisablesInheritedExtensions(): void
    {
        $configuration = $this->configuration([
            'base' => ['generators' => ['repository.index']],
            'analysis-only' => ['extends' => 'base', 'generators' => []],
        ], 'analysis-only');

        $result = (new BuildProfileResolver())->resolve($configuration);

        self::assertTrue($result->isSuccessful());
        self::assertSame([], $result->profile?->generators);
    }

    public function testResolvesMultiLevelInheritanceDeterministically(): void
    {
        $configuration = $this->configuration([
            'base' => ['analyzers' => ['metadata.completeness']],
            'development' => ['extends' => 'base', 'reporters' => ['report.markdown']],
            'ci' => ['extends' => 'development', 'execution' => ['strict' => true]],
        ], 'ci');

        $result = (new BuildProfileResolver())->resolve($configuration);

        self::assertTrue($result->isSuccessful());
        self::assertSame(['metadata.completeness'], $result->profile?->analyzers);
        self::assertSame(['report.markdown'], $result->profile?->reporters);
        self::assertTrue($result->profile?->strict);
    }

    public function testReportsAnUnknownSelectedProfile(): void
    {
        $result = (new BuildProfileResolver())->resolve(RepositoryConfiguration::builtInDefault(), 'missing');

        self::assertFalse($result->isSuccessful());
        self::assertSame('CONFIG-106', $result->diagnostics[0]->code);
    }

    public function testReportsAMissingParentProfile(): void
    {
        $configuration = $this->configuration([
            'release' => ['extends' => 'base'],
        ], 'release');

        $result = (new BuildProfileResolver())->resolve($configuration);

        self::assertFalse($result->isSuccessful());
        self::assertSame('CONFIG-107', $result->diagnostics[0]->code);
    }

    public function testReportsAnInheritanceCycle(): void
    {
        $configuration = $this->configuration([
            'alpha' => ['extends' => 'beta'],
            'beta' => ['extends' => 'gamma'],
            'gamma' => ['extends' => 'alpha'],
        ], 'alpha');

        $result = (new BuildProfileResolver())->resolve($configuration);

        self::assertFalse($result->isSuccessful());
        self::assertSame('CONFIG-108', $result->diagnostics[0]->code);
        self::assertStringContainsString('alpha -> beta -> gamma -> alpha', $result->diagnostics[0]->message);
    }

    public function testRejectsUnknownProfileFields(): void
    {
        $configuration = $this->configuration([
            'default' => ['unexpected' => true],
        ], 'default');

        $result = (new BuildProfileResolver())->resolve($configuration);

        self::assertFalse($result->isSuccessful());
        self::assertSame('CONFIG-105', $result->diagnostics[0]->code);
    }

    /**
     * @param array<string, array<string, mixed>> $profiles
     */
    private function configuration(array $profiles, string $defaultProfile): RepositoryConfiguration
    {
        return new RepositoryConfiguration(
            schemaVersion: '1.0',
            defaultProfile: $defaultProfile,
            profiles: $profiles,
            sourcePath: '.sif/builder.json',
        );
    }
}
