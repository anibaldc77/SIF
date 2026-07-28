<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration;

use Sif\Foundation\Configuration\Contracts\TypedConfigurationInterface;
use Sif\Foundation\Configuration\Exceptions\ConfigurationNotFoundException;
use Sif\Foundation\Configuration\Exceptions\ConfigurationTypeMismatchException;

final readonly class ImmutableConfigurationRepository implements TypedConfigurationInterface
{
    /** @var array<array-key, mixed> */
    private array $values;

    /** @param array<array-key, mixed> $values */
    public function __construct(
        array $values = [],
        ?ConfigurationValueValidator $validator = null,
    ) {
        ($validator ?? new ConfigurationValueValidator())->assertSupported($values);
        $this->values = $values;
    }

    public function has(string $key): bool
    {
        return $this->lookup($key)->isFound();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->lookup($key)->valueOr($default);
    }

    public function require(string $key): mixed
    {
        return $this->lookup($key)->require();
    }

    /** @return array<array-key, mixed> */
    public function all(): array
    {
        return $this->values;
    }

    public function isFrozen(): bool
    {
        return true;
    }

    public function lookup(string|ConfigurationKey $key): ConfigurationLookupResult
    {
        $key = $key instanceof ConfigurationKey ? $key : new ConfigurationKey($key);
        $cursor = $this->values;
        $segments = $key->segments();
        $lastIndex = count($segments) - 1;

        foreach ($segments as $index => $segment) {
            if (!array_key_exists($segment, $cursor)) {
                return ConfigurationLookupResult::missing($key);
            }

            $value = $cursor[$segment];

            if ($index === $lastIndex) {
                return ConfigurationLookupResult::found($key, $value);
            }

            if (!is_array($value)) {
                return ConfigurationLookupResult::missing($key);
            }

            $cursor = $value;
        }

        return ConfigurationLookupResult::missing($key);
    }

    public function string(string|ConfigurationKey $key): string
    {
        $value = $this->requiredValue($key);

        if (!is_string($value)) {
            throw $this->typeMismatch($key, 'string', $value);
        }

        return $value;
    }

    public function integer(string|ConfigurationKey $key): int
    {
        $value = $this->requiredValue($key);

        if (!is_int($value)) {
            throw $this->typeMismatch($key, 'integer', $value);
        }

        return $value;
    }

    public function float(string|ConfigurationKey $key): float
    {
        $value = $this->requiredValue($key);

        if (!is_float($value)) {
            throw $this->typeMismatch($key, 'float', $value);
        }

        return $value;
    }

    public function boolean(string|ConfigurationKey $key): bool
    {
        $value = $this->requiredValue($key);

        if (!is_bool($value)) {
            throw $this->typeMismatch($key, 'boolean', $value);
        }

        return $value;
    }

    /** @return array<array-key, mixed> */
    public function array(string|ConfigurationKey $key): array
    {
        $value = $this->requiredValue($key);

        if (!is_array($value)) {
            throw $this->typeMismatch($key, 'array', $value);
        }

        return $value;
    }

    public function nullableString(string|ConfigurationKey $key): ?string
    {
        $value = $this->requiredValue($key);

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw $this->typeMismatch($key, 'string or null', $value);
        }

        return $value;
    }

    private function requiredValue(string|ConfigurationKey $key): mixed
    {
        return $this->lookup($key)->require();
    }

    private function typeMismatch(
        string|ConfigurationKey $key,
        string $expected,
        mixed $actual,
    ): ConfigurationTypeMismatchException {
        $normalized = $key instanceof ConfigurationKey
            ? $key->value()
            : (new ConfigurationKey($key))->value();

        return ConfigurationTypeMismatchException::forKey(
            $normalized,
            $expected,
            $actual,
        );
    }
}
