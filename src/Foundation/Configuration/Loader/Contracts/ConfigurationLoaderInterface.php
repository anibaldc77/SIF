<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Loader\Contracts;

interface ConfigurationLoaderInterface
{
    public function supports(string $source): bool;

    /**
     * @return array<array-key, mixed>
     */
    public function load(string $source): array;
}
