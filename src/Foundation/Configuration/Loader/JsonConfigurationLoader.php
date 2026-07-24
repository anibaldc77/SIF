<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Loader;

use JsonException;
use Sif\Foundation\Configuration\Exceptions\ConfigurationSourceNotFoundException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceException;
use Sif\Foundation\Configuration\Exceptions\UnreadableConfigurationSourceException;
use Sif\Foundation\Configuration\Loader\Contracts\ConfigurationLoaderInterface;

final class JsonConfigurationLoader implements ConfigurationLoaderInterface
{
    public function supports(string $source): bool
    {
        return strtolower(pathinfo($source, PATHINFO_EXTENSION)) === 'json';
    }

    /**
     * @return array<array-key, mixed>
     */
    public function load(string $source): array
    {
        $this->assertReadable($source);
        $contents = file_get_contents($source);

        if ($contents === false) {
            throw UnreadableConfigurationSourceException::forSource($source);
        }

        try {
            $values = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $cause) {
            throw InvalidConfigurationSourceException::invalidJson($source, $cause);
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
