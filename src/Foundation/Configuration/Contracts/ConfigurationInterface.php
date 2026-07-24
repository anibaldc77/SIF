<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Contracts;

interface ConfigurationInterface
{
    public function has(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function require(string $key): mixed;

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array;

    public function isFrozen(): bool;
}
