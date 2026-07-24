<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Loader;

use Sif\Foundation\Configuration\Exceptions\UnsupportedConfigurationSourceException;
use Sif\Foundation\Configuration\Loader\Contracts\ConfigurationLoaderInterface;

final class ConfigurationFileLoader
{
    /** @var list<ConfigurationLoaderInterface> */
    private array $loaders;

    private ConfigurationMerger $merger;

    /**
     * @param iterable<ConfigurationLoaderInterface> $loaders
     */
    public function __construct(iterable $loaders, ?ConfigurationMerger $merger = null)
    {
        $this->loaders = [];

        foreach ($loaders as $loader) {
            $this->loaders[] = $loader;
        }

        $this->merger = $merger ?? new ConfigurationMerger();
    }

    public static function withDefaultLoaders(): self
    {
        return new self([
            new PhpConfigurationLoader(),
            new JsonConfigurationLoader(),
        ]);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function load(string $source): array
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($source)) {
                return $loader->load($source);
            }
        }

        throw UnsupportedConfigurationSourceException::forSource($source);
    }

    /**
     * Sources are processed from lowest to highest precedence.
     *
     * @param iterable<string> $sources
     *
     * @return array<array-key, mixed>
     */
    public function loadMany(iterable $sources): array
    {
        $loaded = [];

        foreach ($sources as $source) {
            $loaded[] = $this->load($source);
        }

        return $this->merger->merge(...$loaded);
    }
}
