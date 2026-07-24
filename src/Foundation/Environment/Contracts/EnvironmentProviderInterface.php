<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Contracts;

interface EnvironmentProviderInterface
{
    public function has(string $key): bool;

    public function get(string $key, ?string $default = null): ?string;

    /**
     * @return array<string, string>
     */
    public function all(): array;
}
