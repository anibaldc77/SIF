<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Loader;

final class ConfigurationMerger
{
    /**
     * Merges sources from lowest to highest precedence.
     *
     * Associative arrays are merged recursively. Lists and scalar values are
     * replaced as complete values by the later source.
     *
     * @param array<array-key, mixed> ...$sources
     *
     * @return array<array-key, mixed>
     */
    public function merge(array ...$sources): array
    {
        $merged = [];

        foreach ($sources as $source) {
            $merged = $this->mergePair($merged, $source);
        }

        return $merged;
    }

    /**
     * @param array<array-key, mixed> $base
     * @param array<array-key, mixed> $override
     *
     * @return array<array-key, mixed>
     */
    private function mergePair(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (
                array_key_exists($key, $base)
                && is_array($base[$key])
                && is_array($value)
                && !$this->isList($base[$key])
                && !$this->isList($value)
            ) {
                $base[$key] = $this->mergePair($base[$key], $value);

                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private function isList(array $value): bool
    {
        return array_is_list($value);
    }
}
