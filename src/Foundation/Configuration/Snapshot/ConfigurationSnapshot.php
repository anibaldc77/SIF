<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Snapshot;

use Sif\Foundation\Configuration\ImmutableConfigurationRepository;

final readonly class ConfigurationSnapshot
{
    public const FORMAT_VERSION = 1;

    public function __construct(
        private ImmutableConfigurationRepository $repository,
        private ConfigurationFingerprint $fingerprint,
        private int $formatVersion = self::FORMAT_VERSION,
    ) {
    }

    public function repository(): ImmutableConfigurationRepository
    {
        return $this->repository;
    }

    public function fingerprint(): ConfigurationFingerprint
    {
        return $this->fingerprint;
    }

    public function formatVersion(): int
    {
        return $this->formatVersion;
    }

    /** @return array<array-key, mixed> */
    public function values(): array
    {
        return $this->repository->all();
    }

    public function matches(self $other): bool
    {
        return $this->formatVersion === $other->formatVersion
            && $this->fingerprint->equals($other->fingerprint);
    }
}
