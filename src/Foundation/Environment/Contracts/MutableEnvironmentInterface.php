<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Contracts;

interface MutableEnvironmentInterface extends EnvironmentProviderInterface
{
    public function set(string $key, string $value): void;

    /** @param array<string, string> $values */
    public function replace(array $values): void;

    public function freeze(): void;

    public function isFrozen(): bool;
}
