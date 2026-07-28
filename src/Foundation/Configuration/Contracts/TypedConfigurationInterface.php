<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Contracts;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ConfigurationLookupResult;

interface TypedConfigurationInterface extends ConfigurationInterface
{
    public function lookup(string|ConfigurationKey $key): ConfigurationLookupResult;

    public function string(string|ConfigurationKey $key): string;

    public function integer(string|ConfigurationKey $key): int;

    public function float(string|ConfigurationKey $key): float;

    public function boolean(string|ConfigurationKey $key): bool;

    /** @return array<array-key, mixed> */
    public function array(string|ConfigurationKey $key): array;

    public function nullableString(string|ConfigurationKey $key): ?string;
}
