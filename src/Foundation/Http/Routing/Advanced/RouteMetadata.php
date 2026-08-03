<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteGroupException;

final readonly class RouteMetadata
{
    /** @var array<string, bool|float|int|string|null|array<array-key, mixed>> */
    private array $values;

    /** @param array<string, bool|float|int|string|null|array<array-key, mixed>> $values */
    public function __construct(array $values = [])
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            if (preg_match('/^[a-z][a-z0-9_.-]*$/', $key) !== 1) {
                throw new RouteGroupException(sprintf('Invalid route metadata key "%s".', $key));
            }
            $normalized[$key] = $value;
        }
        ksort($normalized);
        $this->values = $normalized;
    }

    /** @return array<string, bool|float|int|string|null|array<array-key, mixed>> */
    public function all(): array
    {
        return $this->values;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /** @return bool|float|int|string|null|array<array-key, mixed> */
    public function get(string $key): bool|float|int|string|null|array
    {
        if (!$this->has($key)) {
            throw new RouteGroupException(sprintf('Route metadata key "%s" is not defined.', $key));
        }

        return $this->values[$key];
    }

    public function merge(self $child): self
    {
        $merged = $this->values;
        foreach ($child->values as $key => $value) {
            if (array_key_exists($key, $merged) && $merged[$key] !== $value) {
                throw new RouteGroupException(sprintf('Conflicting route metadata key "%s".', $key));
            }
            $merged[$key] = $value;
        }

        return new self($merged);
    }
}
