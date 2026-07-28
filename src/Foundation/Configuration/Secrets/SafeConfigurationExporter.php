<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Contracts\ConfigurationInterface;
use Sif\Foundation\Configuration\Secrets\Contracts\ConfigurationRedactionPolicyInterface;
use Sif\Foundation\Configuration\Secrets\Contracts\ConfigurationSecretClassifierInterface;

final readonly class SafeConfigurationExporter
{
    public function __construct(
        private ConfigurationSecretClassifierInterface $classifier,
        private ConfigurationRedactionPolicyInterface $redactionPolicy = new FixedMarkerConfigurationRedactionPolicy(),
    ) {
    }

    public function export(ConfigurationInterface $configuration): SafeConfigurationExport
    {
        $redactedKeys = [];
        $values = $this->exportArray($configuration->all(), null, $redactedKeys);

        return new SafeConfigurationExport($values, $redactedKeys);
    }

    /**
     * @param array<array-key, mixed> $values
     * @param list<string> $redactedKeys
     * @return array<array-key, mixed>
     */
    private function exportArray(array $values, ?string $parent, array &$redactedKeys): array
    {
        $exported = [];

        foreach ($values as $segment => $value) {
            $segment = (string) $segment;
            $path = $parent === null ? $segment : $parent . '.' . $segment;
            $key = new ConfigurationKey($path);

            if ($this->classifier->classify($key)->isSecret()) {
                $exported[$segment] = $this->redactionPolicy->redact($key, $value);
                $redactedKeys[] = $key->value();
                continue;
            }

            $exported[$segment] = is_array($value)
                ? $this->exportArray($value, $path, $redactedKeys)
                : $value;
        }

        return $exported;
    }
}
