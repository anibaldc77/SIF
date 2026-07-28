<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\Composition\ConfigurationComposer;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;
use Sif\Foundation\Configuration\Source\ArrayConfigurationSource;

final class ConfigurationSourceAndCompositionTest extends TestCase
{
    public function testArraySourceExposesStableMetadataAndValues(): void
    {
        $source = new ArrayConfigurationSource(
            'defaults',
            ['app' => ['name' => 'SIF']],
            10,
        );

        $result = $source->load();

        self::assertSame('defaults', $source->id());
        self::assertSame('array', $source->type());
        self::assertSame(10, $source->precedence());
        self::assertSame('defaults', $result->sourceId());
        self::assertSame(['app' => ['name' => 'SIF']], $result->values());
    }

    public function testEmptySourceIdentifierIsRejected(): void
    {
        $this->expectException(InvalidConfigurationSourceDefinitionException::class);

        new ArrayConfigurationSource('   ', []);
    }

    public function testHigherPrecedenceOverridesLowerPrecedenceRegardlessOfRegistrationOrder(): void
    {
        $composer = new ConfigurationComposer();
        $composed = $composer->compose([
            new ArrayConfigurationSource('environment', [
                'app' => ['debug' => true],
            ], 100),
            new ArrayConfigurationSource('defaults', [
                'app' => ['name' => 'SIF', 'debug' => false],
            ], 0),
        ]);

        self::assertSame('SIF', $composed->repository()->string('app.name'));
        self::assertTrue($composed->repository()->boolean('app.debug'));
        $provenance = $composed->provenance('app.debug');
        self::assertNotNull($provenance);
        self::assertSame('environment', $provenance->sourceId());
        self::assertTrue($provenance->overrodeEarlierValue());
    }

    public function testEqualPrecedenceUsesStableRegistrationOrder(): void
    {
        $composed = (new ConfigurationComposer())->compose([
            new ArrayConfigurationSource('first', ['value' => 1], 10),
            new ArrayConfigurationSource('second', ['value' => 2], 10),
        ]);

        self::assertSame(2, $composed->repository()->integer('value'));
        $provenance = $composed->provenance('value');
        self::assertNotNull($provenance);
        self::assertSame('second', $provenance->sourceId());
        self::assertSame(1, $provenance->registrationOrder());
    }

    public function testAssociativeMapsMergeWhileListsReplace(): void
    {
        $composed = (new ConfigurationComposer())->compose([
            new ArrayConfigurationSource('defaults', [
                'database' => ['host' => 'localhost', 'port' => 3306],
                'modules' => ['runtime', 'events'],
            ]),
            new ArrayConfigurationSource('override', [
                'database' => ['port' => 5432],
                'modules' => ['runtime'],
            ], 10),
        ]);

        self::assertSame('localhost', $composed->repository()->string('database.host'));
        self::assertSame(5432, $composed->repository()->integer('database.port'));
        self::assertSame(['runtime'], $composed->repository()->array('modules'));
        self::assertSame('defaults', $composed->provenance('database.host')?->sourceId());
        self::assertSame('override', $composed->provenance('database.port')?->sourceId());
        self::assertSame('override', $composed->provenance('modules')?->sourceId());
    }

    public function testEmptyIncomingArrayReplacesEarlierMap(): void
    {
        $composed = (new ConfigurationComposer())->compose([
            new ArrayConfigurationSource('defaults', ['features' => ['audit' => true]]),
            new ArrayConfigurationSource('override', ['features' => []], 10),
        ]);

        self::assertSame([], $composed->repository()->array('features'));
        self::assertSame('override', $composed->provenance('features')?->sourceId());
    }

    public function testMissingProvenanceReturnsNull(): void
    {
        $composed = (new ConfigurationComposer())->compose([]);

        self::assertNull($composed->provenance('missing'));
        self::assertSame([], $composed->allProvenance());
    }
}
