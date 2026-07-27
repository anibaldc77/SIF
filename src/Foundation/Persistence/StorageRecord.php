<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidStorageRecordKeyException;
use Sif\Foundation\Exceptions\UnsupportedStorageRecordValueException;

final readonly class StorageRecord
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
        foreach ($values as $key => &$value) {
            if (trim($key) === '') {
                throw new InvalidStorageRecordKeyException(
                    'Storage record keys cannot be empty.',
                );
            }

            self::assertCompatibleValue($value, '$.' . $key);
        }

        unset($value);

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

    private static function assertCompatibleValue(
        mixed &$value,
        string $path,
    ): void {
        if (
            $value === null
            || is_bool($value)
            || is_int($value)
            || is_string($value)
        ) {
            return;
        }

        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new UnsupportedStorageRecordValueException(
                    sprintf(
                        'Storage record value at "%s" must be finite.',
                        $path,
                    ),
                );
            }

            return;
        }

        if (!is_array($value)) {
            throw new UnsupportedStorageRecordValueException(
                sprintf(
                    'Unsupported storage record value of type "%s" at "%s".',
                    get_debug_type($value),
                    $path,
                ),
            );
        }

        foreach ($value as $key => &$nestedValue) {
            if (is_string($key) && trim($key) === '') {
                throw new InvalidStorageRecordKeyException(
                    sprintf(
                        'Storage record key at "%s" cannot be empty.',
                        $path,
                    ),
                );
            }

            self::assertCompatibleValue(
                $nestedValue,
                $path . '[' . (string) $key . ']',
            );
        }

        unset($nestedValue);
    }
}
