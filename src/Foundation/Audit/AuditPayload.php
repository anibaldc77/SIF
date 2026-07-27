<?php

declare(strict_types=1);

namespace Sif\Foundation\Audit;

use Sif\Foundation\Exceptions\InvalidAuditPayloadKeyException;
use Sif\Foundation\Exceptions\UnsupportedAuditPayloadValueException;

final readonly class AuditPayload
{
    /**
     * @var array<string, mixed>
     */
    private array $values;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        self::assertCompatibleMap($values, '$');

        $this->values = $values;
    }

    public function isEmpty(): bool
    {
        return $this->values === [];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }

    public function merged(self $overrides): self
    {
        if ($overrides->isEmpty()) {
            return $this;
        }

        return new self(array_replace($this->values, $overrides->values));
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function assertCompatibleMap(array &$values, string $path): void
    {
        foreach ($values as $key => &$value) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidAuditPayloadKeyException(
                    sprintf('Audit payload key at "%s" must be a non-empty string.', $path),
                );
            }

            self::assertCompatibleValue($value, $path . '.' . $key);
        }

        unset($value);
    }

    private static function assertCompatibleValue(mixed &$value, string $path): void
    {
        if ($value === null || is_bool($value) || is_int($value) || is_string($value)) {
            return;
        }

        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new UnsupportedAuditPayloadValueException(
                    sprintf('Audit payload value at "%s" must be finite.', $path),
                );
            }

            return;
        }

        if (!is_array($value)) {
            throw new UnsupportedAuditPayloadValueException(
                sprintf(
                    'Unsupported audit payload value of type "%s" at "%s".',
                    get_debug_type($value),
                    $path,
                ),
            );
        }

        self::assertCompatibleArray($value, $path);
    }

    /**
     * @param array<mixed> $values
     */
    private static function assertCompatibleArray(array &$values, string $path): void
    {
        foreach ($values as $key => &$value) {
            if (is_string($key) && trim($key) === '') {
                throw new InvalidAuditPayloadKeyException(
                    sprintf(
                        'Audit payload key at "%s" cannot be empty.',
                        $path,
                    ),
                );
            }

            self::assertCompatibleValue(
                $value,
                $path . '[' . (string) $key . ']',
            );
        }

        unset($value);
    }
}
