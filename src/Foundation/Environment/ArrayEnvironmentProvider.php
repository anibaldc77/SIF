<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment;

use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentKeyException;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentValueException;

final class ArrayEnvironmentProvider implements EnvironmentProviderInterface
{
    /** @var array<string, string> */
    private array $values;

    /**
     * @param array<array-key, mixed> $values
     */
    public function __construct(array $values = [])
    {
        $this->values = self::normalize($values);
    }

    public function has(string $key): bool
    {
        return array_key_exists(self::key($key), $this->values);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $key = self::key($key);

        return $this->values[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->values;
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<string, string>
     */
    public static function normalize(array $values): array
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            $key = self::key((string) $key);

            if ($value === null) {
                continue;
            }

            if (!is_scalar($value)) {
                throw InvalidEnvironmentValueException::forKey($key);
            }

            $normalized[$key] = self::stringify($value);
        }

        return $normalized;
    }

    private static function key(string $key): string
    {
        $key = trim($key);

        if ($key === '') {
            throw InvalidEnvironmentKeyException::empty();
        }

        return $key;
    }

    private static function stringify(bool|float|int|string $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}
