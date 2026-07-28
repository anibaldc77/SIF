<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Snapshot;

use Sif\Foundation\Configuration\Contracts\ConfigurationInterface;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;

final readonly class ConfigurationSnapshotFactory
{
    public function __construct(
        private CanonicalConfigurationSerializer $serializer = new CanonicalConfigurationSerializer(),
    ) {
    }

    public function create(ConfigurationInterface $configuration): ConfigurationSnapshot
    {
        $repository = new ImmutableConfigurationRepository($configuration->all());
        $payload = $this->serializer->serialize($repository);

        return new ConfigurationSnapshot(
            $repository,
            ConfigurationFingerprint::fromCanonicalPayload($payload),
        );
    }

    public function verify(ConfigurationSnapshot $snapshot): bool
    {
        $actual = ConfigurationFingerprint::fromCanonicalPayload(
            $this->serializer->serialize($snapshot->repository()),
        );

        return $snapshot->fingerprint()->equals($actual);
    }
}
