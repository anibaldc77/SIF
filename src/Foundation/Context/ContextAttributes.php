<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use JsonException;
use Sif\Foundation\Exceptions\InvalidContextAttributeKeyException;
use Sif\Foundation\Exceptions\UnsupportedContextAttributeValueException;

/**
 * Immutable collection of deterministic context attribute values.
 *
 */
final readonly class ContextAttributes
{
    /** @var array<string, mixed> */
    private array $values;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        self::assertJsonCompatible($values);

        foreach ($values as $key => $value) {
            self::assertTopLevelKey($key);
            self::assertValue($value, $key);
        }

        $this->values = $values;
    }

    public static function empty(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return $this->values === [];
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /** @return mixed */
    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values;
    }

    private static function assertValue(mixed $value, string $path): void
    {
        if ($value === null || is_bool($value) || is_int($value) || is_string($value)) {
            return;
        }

        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new UnsupportedContextAttributeValueException(
                    sprintf('Context attribute "%s" contains a non-finite float.', $path),
                );
            }

            return;
        }

        if (is_array($value)) {
            foreach ($value as $key => $nestedValue) {
                if (is_string($key) && trim($key) === '') {
                    throw new InvalidContextAttributeKeyException(
                        sprintf('Context attribute key at "%s" must not be empty.', $path),
                    );
                }

                self::assertValue($nestedValue, $path . '.' . (string) $key);
            }

            return;
        }

        throw new UnsupportedContextAttributeValueException(
            sprintf('Context attribute "%s" contains unsupported type "%s".', $path, get_debug_type($value)),
        );
    }

    private static function assertTopLevelKey(int|string $key): void
    {
        if (!is_string($key) || trim($key) === '') {
            throw new InvalidContextAttributeKeyException('Context attribute keys must be non-empty strings.');
        }
    }

    /** @param array<string, mixed> $values */
    private static function assertJsonCompatible(array $values): void
    {
        try {
            json_encode($values, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnsupportedContextAttributeValueException(
                'Context attributes must form a finite, non-recursive JSON-compatible structure.',
                previous: $exception,
            );
        }
    }
}
