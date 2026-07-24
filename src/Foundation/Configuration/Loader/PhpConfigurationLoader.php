<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Loader;

use Sif\Foundation\Configuration\Exceptions\ConfigurationSourceNotFoundException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceException;
use Sif\Foundation\Configuration\Exceptions\UnreadableConfigurationSourceException;
use Sif\Foundation\Configuration\Loader\Contracts\ConfigurationLoaderInterface;
use Throwable;

final class PhpConfigurationLoader implements ConfigurationLoaderInterface
{
    public function supports(string $source): bool
    {
        return strtolower(pathinfo($source, PATHINFO_EXTENSION)) === 'php';
    }

    /**
     * @return array<array-key, mixed>
     */
    public function load(string $source): array
    {
        $this->assertReadable($source);

        try {
            $values = (static fn (string $path): mixed => require $path)($source);
        } catch (Throwable $cause) {
            throw InvalidConfigurationSourceException::cannotLoad($source, $cause);
        }

        if (!is_array($values)) {
            throw InvalidConfigurationSourceException::nonArrayResult($source);
        }

        return $values;
    }

    private function assertReadable(string $source): void
    {
        if (!is_file($source)) {
            throw ConfigurationSourceNotFoundException::forSource($source);
        }

        if (!is_readable($source)) {
            throw UnreadableConfigurationSourceException::forSource($source);
        }
    }
}
