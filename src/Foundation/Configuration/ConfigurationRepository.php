<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration;

use Sif\Foundation\Configuration\Contracts\MutableConfigurationInterface;
use Sif\Foundation\Configuration\Exceptions\ConfigurationNotFoundException;
use Sif\Foundation\Configuration\Exceptions\FrozenConfigurationException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationKeyException;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationStructureException;

final class ConfigurationRepository implements MutableConfigurationInterface
{
    /** @var array<array-key, mixed> */
    private array $values;

    private bool $frozen = false;

    /**
     * @param array<array-key, mixed> $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function has(string $key): bool
    {
        [$found] = $this->resolve($key);

        return $found;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        [$found, $value] = $this->resolve($key);

        return $found ? $value : $default;
    }

    public function require(string $key): mixed
    {
        $normalizedKey = $this->normalizeKey($key);
        [$found, $value] = $this->resolveNormalized($normalizedKey);

        if (!$found) {
            throw ConfigurationNotFoundException::forKey($normalizedKey);
        }

        return $value;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }

    public function set(string $key, mixed $value): void
    {
        $this->assertMutable();
        $normalizedKey = $this->normalizeKey($key);
        $segments = explode('.', $normalizedKey);
        $lastSegment = array_pop($segments);

        $cursor =& $this->values;
        $visited = [];

        foreach ($segments as $segment) {
            $visited[] = $segment;

            if (!array_key_exists($segment, $cursor)) {
                $cursor[$segment] = [];
            }

            if (!is_array($cursor[$segment])) {
                throw InvalidConfigurationStructureException::cannotDescend(
                    $normalizedKey,
                    implode('.', $visited),
                );
            }

            $cursor =& $cursor[$segment];
        }

        $cursor[$lastSegment] = $value;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    public function replace(array $values): void
    {
        $this->assertMutable();
        $this->values = $values;
    }

    public function freeze(): void
    {
        $this->frozen = true;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * @return array{bool, mixed}
     */
    private function resolve(string $key): array
    {
        return $this->resolveNormalized($this->normalizeKey($key));
    }

    /**
     * @return array{bool, mixed}
     */
    private function resolveNormalized(string $key): array
    {
        $cursor = $this->values;
        $segments = explode('.', $key);
        $lastIndex = count($segments) - 1;

        foreach ($segments as $index => $segment) {
            if (!array_key_exists($segment, $cursor)) {
                return [false, null];
            }

            $value = $cursor[$segment];

            if ($index === $lastIndex) {
                return [true, $value];
            }

            if (!is_array($value)) {
                return [false, null];
            }

            $cursor = $value;
        }

        return [false, null];
    }

    private function normalizeKey(string $key): string
    {
        $key = trim($key);

        if ($key === '') {
            throw InvalidConfigurationKeyException::empty();
        }

        foreach (explode('.', $key) as $segment) {
            if ($segment === '') {
                throw InvalidConfigurationKeyException::emptySegment($key);
            }
        }

        return $key;
    }

    private function assertMutable(): void
    {
        if ($this->frozen) {
            throw FrozenConfigurationException::mutationAttempted();
        }
    }
}
