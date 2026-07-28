<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Cache;

use InvalidArgumentException;
use Sif\Foundation\Configuration\Cache\Contracts\ConfigurationSnapshotCacheInterface;
use Sif\Foundation\Configuration\Snapshot\ConfigurationSnapshot;

final class InMemoryConfigurationSnapshotCache implements ConfigurationSnapshotCacheInterface
{
    /** @var array<string, ConfigurationSnapshot> */
    private array $snapshots = [];

    public function get(string $key): ?ConfigurationSnapshot
    {
        return $this->snapshots[$this->normalizeKey($key)] ?? null;
    }

    public function put(string $key, ConfigurationSnapshot $snapshot): void
    {
        $this->snapshots[$this->normalizeKey($key)] = $snapshot;
    }

    public function forget(string $key): void
    {
        unset($this->snapshots[$this->normalizeKey($key)]);
    }

    public function clear(): void
    {
        $this->snapshots = [];
    }

    private function normalizeKey(string $key): string
    {
        $key = trim($key);

        if ($key === '') {
            throw new InvalidArgumentException('Configuration cache key cannot be empty.');
        }

        return $key;
    }
}
