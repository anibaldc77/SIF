<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Snapshot;

use JsonException;
use Sif\Foundation\Configuration\Contracts\ConfigurationInterface;
use Sif\Foundation\Configuration\Exceptions\UnsupportedConfigurationValueException;
use Sif\Foundation\Configuration\ConfigurationValueValidator;

final readonly class CanonicalConfigurationSerializer
{
    public function __construct(
        private ConfigurationValueValidator $validator = new ConfigurationValueValidator(),
    ) {
    }

    /**
     * Produces a deterministic, type-preserving representation.
     *
     * The returned payload may contain secrets and must not be logged or exposed.
     *
     * @param ConfigurationInterface|array<array-key, mixed> $configuration
     * @throws JsonException
     * @throws UnsupportedConfigurationValueException
     */
    public function serialize(ConfigurationInterface|array $configuration): string
    {
        $values = $configuration instanceof ConfigurationInterface
            ? $configuration->all()
            : $configuration;

        $this->validator->assertSupported($values);

        return json_encode(
            $this->canonicalNode($values),
            JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_PRESERVE_ZERO_FRACTION,
        );
    }

    /** @return array<string, mixed> */
    private function canonicalNode(mixed $value): array
    {
        if ($value === null) {
            return ['type' => 'null'];
        }

        if (is_bool($value)) {
            return ['type' => 'boolean', 'value' => $value];
        }

        if (is_int($value)) {
            return ['type' => 'integer', 'value' => $value];
        }

        if (is_float($value)) {
            return ['type' => 'float', 'value' => $value];
        }

        if (is_string($value)) {
            return ['type' => 'string', 'value' => $value];
        }

        if (array_is_list($value)) {
            return [
                'type' => 'list',
                'items' => array_map(
                    fn (mixed $item): array => $this->canonicalNode($item),
                    $value,
                ),
            ];
        }

        $entries = [];
        foreach ($value as $key => $item) {
            $entries[] = [
                'sort' => (is_int($key) ? 'i:' : 's:') . (string) $key,
                'key' => [
                    'type' => is_int($key) ? 'integer' : 'string',
                    'value' => $key,
                ],
                'value' => $this->canonicalNode($item),
            ];
        }

        usort(
            $entries,
            static fn (array $left, array $right): int => $left['sort'] <=> $right['sort'],
        );

        return [
            'type' => 'map',
            'entries' => array_map(
                static fn (array $entry): array => [
                    'key' => $entry['key'],
                    'value' => $entry['value'],
                ],
                $entries,
            ),
        ];
    }
}
