<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Cache\Contracts;

use Sif\Foundation\Configuration\Snapshot\ConfigurationSnapshot;

interface ConfigurationSnapshotCacheInterface
{
    public function get(string $key): ?ConfigurationSnapshot;

    public function put(string $key, ConfigurationSnapshot $snapshot): void;

    public function forget(string $key): void;

    public function clear(): void;
}
