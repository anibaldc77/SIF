<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Secrets\Contracts\ConfigurationSecretClassifierInterface;

final readonly class CompositeConfigurationSecretClassifier implements ConfigurationSecretClassifierInterface
{
    /** @var list<ConfigurationSecretClassifierInterface> */
    private array $classifiers;

    /** @param iterable<ConfigurationSecretClassifierInterface> $classifiers */
    public function __construct(iterable $classifiers)
    {
        $normalized = [];

        foreach ($classifiers as $classifier) {
            $normalized[] = $classifier;
        }

        $this->classifiers = $normalized;
    }

    public function classify(ConfigurationKey $key): ConfigurationSecretClassification
    {
        foreach ($this->classifiers as $classifier) {
            if ($classifier->classify($key)->isSecret()) {
                return ConfigurationSecretClassification::Secret;
            }
        }

        return ConfigurationSecretClassification::Public;
    }
}
