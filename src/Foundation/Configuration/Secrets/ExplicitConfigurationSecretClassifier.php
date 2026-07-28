<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Secrets\Contracts\ConfigurationSecretClassifierInterface;

final readonly class ExplicitConfigurationSecretClassifier implements ConfigurationSecretClassifierInterface
{
    /** @var array<string, true> */
    private array $secretKeys;

    /** @var list<string> */
    private array $secretPrefixes;

    /**
     * @param iterable<string|ConfigurationKey> $secretKeys
     * @param iterable<string|ConfigurationKey> $secretPrefixes
     */
    public function __construct(
        iterable $secretKeys = [],
        iterable $secretPrefixes = [],
    ) {
        $keys = [];

        foreach ($secretKeys as $key) {
            $normalized = $key instanceof ConfigurationKey ? $key : new ConfigurationKey($key);
            $keys[$normalized->value()] = true;
        }

        $prefixes = [];

        foreach ($secretPrefixes as $prefix) {
            $normalized = $prefix instanceof ConfigurationKey ? $prefix : new ConfigurationKey($prefix);
            $prefixes[] = $normalized->value();
        }

        $this->secretKeys = $keys;
        $this->secretPrefixes = array_values(array_unique($prefixes));
    }

    public function classify(ConfigurationKey $key): ConfigurationSecretClassification
    {
        if (isset($this->secretKeys[$key->value()])) {
            return ConfigurationSecretClassification::Secret;
        }

        foreach ($this->secretPrefixes as $prefix) {
            if ($key->value() === $prefix || str_starts_with($key->value(), $prefix . '.')) {
                return ConfigurationSecretClassification::Secret;
            }
        }

        return ConfigurationSecretClassification::Public;
    }
}
