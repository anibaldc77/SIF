<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Contracts;

interface MutableConfigurationInterface extends ConfigurationInterface
{
    public function set(string $key, mixed $value): void;

    /**
     * @param array<array-key, mixed> $values
     */
    public function replace(array $values): void;

    public function freeze(): void;
}
