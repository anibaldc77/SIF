<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Configuration;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;
use Sif\Foundation\Configuration\Snapshot\CanonicalConfigurationSerializer;
use Sif\Foundation\Configuration\Snapshot\ConfigurationFingerprint;
use Sif\Foundation\Configuration\Snapshot\ConfigurationSnapshot;
use Sif\Foundation\Configuration\Snapshot\ConfigurationSnapshotFactory;

final class ConfigurationSnapshotTest extends TestCase
{
    public function testAssociativeMapOrderDoesNotChangeCanonicalSerialization(): void
    {
        $serializer = new CanonicalConfigurationSerializer();

        $left = $serializer->serialize(['app' => ['name' => 'SIF', 'debug' => true], 'workers' => 4]);
        $right = $serializer->serialize(['workers' => 4, 'app' => ['debug' => true, 'name' => 'SIF']]);

        self::assertSame($left, $right);
    }

    public function testListOrderRemainsSignificant(): void
    {
        $serializer = new CanonicalConfigurationSerializer();

        self::assertNotSame(
            $serializer->serialize(['modules' => ['audit', 'events']]),
            $serializer->serialize(['modules' => ['events', 'audit']]),
        );
    }

    public function testCanonicalSerializationPreservesScalarTypes(): void
    {
        $serializer = new CanonicalConfigurationSerializer();

        self::assertNotSame(
            $serializer->serialize(['value' => 1]),
            $serializer->serialize(['value' => 1.0]),
        );
        self::assertNotSame(
            $serializer->serialize(['value' => 1]),
            $serializer->serialize(['value' => '1']),
        );
    }

    public function testEquivalentRepositoriesProduceMatchingSnapshots(): void
    {
        $factory = new ConfigurationSnapshotFactory();

        $left = $factory->create(new ImmutableConfigurationRepository([
            'database' => ['host' => 'localhost', 'port' => 5432],
            'debug' => false,
        ]));
        $right = $factory->create(new ImmutableConfigurationRepository([
            'debug' => false,
            'database' => ['port' => 5432, 'host' => 'localhost'],
        ]));

        self::assertTrue($left->matches($right));
        self::assertTrue($factory->verify($left));
        self::assertSame(ConfigurationSnapshot::FORMAT_VERSION, $left->formatVersion());
    }

    public function testChangedValueProducesDifferentFingerprint(): void
    {
        $factory = new ConfigurationSnapshotFactory();
        $left = $factory->create(new ImmutableConfigurationRepository(['debug' => false]));
        $right = $factory->create(new ImmutableConfigurationRepository(['debug' => true]));

        self::assertFalse($left->matches($right));
        self::assertSame('sha256', $left->fingerprint()->algorithm());
        self::assertStringStartsWith('sha256:', $left->fingerprint()->value());
    }

    public function testSnapshotOwnsAnImmutableCopyOfInputValues(): void
    {
        $values = ['app' => ['name' => 'SIF']];
        $snapshot = (new ConfigurationSnapshotFactory())->create(
            new ImmutableConfigurationRepository($values),
        );
        $values['app']['name'] = 'Changed';

        self::assertSame('SIF', $snapshot->repository()->string('app.name'));
    }

    public function testFingerprintRejectsMalformedDigest(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConfigurationFingerprint('not-a-sha256-digest');
    }
}
