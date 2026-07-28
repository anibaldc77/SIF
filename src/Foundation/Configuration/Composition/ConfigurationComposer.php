<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Composition;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;
use Sif\Foundation\Configuration\Loader\ConfigurationMerger;
use Sif\Foundation\Configuration\Source\ConfigurationSourceResult;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;

final readonly class ConfigurationComposer
{
    public function __construct(
        private ConfigurationMerger $merger = new ConfigurationMerger(),
    ) {
    }

    /** @param list<ConfigurationSourceInterface> $sources */
    public function compose(array $sources): ComposedConfiguration
    {
        $ordered = [];

        foreach ($sources as $registrationOrder => $source) {
            $ordered[] = [
                'order' => $registrationOrder,
                'result' => $source->load(),
            ];
        }

        usort(
            $ordered,
            static fn (array $left, array $right): int =>
                $left['result']->precedence() <=> $right['result']->precedence()
                ?: $left['order'] <=> $right['order'],
        );

        $values = [];
        $provenance = [];
        $diagnostics = [];

        foreach ($ordered as $entry) {
            /** @var ConfigurationSourceResult $result */
            $result = $entry['result'];
            /** @var int $registrationOrder */
            $registrationOrder = $entry['order'];
            $values = $this->merger->merge($values, $result->values());
            array_push($diagnostics, ...$result->diagnostics());
            $this->recordLeafProvenance(
                $result->values(),
                $result,
                $registrationOrder,
                $provenance,
            );
        }

        return new ComposedConfiguration(
            new ImmutableConfigurationRepository($values),
            $provenance,
            $diagnostics,
        );
    }

    /**
     * @param array<array-key, mixed> $values
     * @param array<string, ConfigurationProvenance> $provenance
     */
    private function recordLeafProvenance(
        array $values,
        ConfigurationSourceResult $source,
        int $registrationOrder,
        array &$provenance,
        string $prefix = '',
    ): void {
        foreach ($values as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value) && $value !== [] && !array_is_list($value)) {
                $this->recordLeafProvenance(
                    $value,
                    $source,
                    $registrationOrder,
                    $provenance,
                    $path,
                );
                continue;
            }

            $normalized = (new ConfigurationKey($path))->value();
            $provenance[$normalized] = new ConfigurationProvenance(
                new ConfigurationKey($normalized),
                $source->sourceId(),
                $source->sourceType(),
                $source->precedence(),
                $registrationOrder,
                array_key_exists($normalized, $provenance),
            );
        }
    }
}
