<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteGroupException;

final readonly class RouteDefaults
{
    /** @var array<string, bool|float|int|string|null> */
    private array $values;

    /** @param array<string, bool|float|int|string|null> $values */
    public function __construct(array $values = [])
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) !== 1) {
                throw new RouteGroupException(sprintf('Invalid route default key "%s".', $key));
            }
            $normalized[$key] = $value;
        }
        ksort($normalized);
        $this->values = $normalized;
    }

    /** @return array<string, bool|float|int|string|null> */
    public function all(): array
    {
        return $this->values;
    }

    public function merge(self $child): self
    {
        $merged = $this->values;
        foreach ($child->values as $key => $value) {
            if (array_key_exists($key, $merged) && $merged[$key] !== $value) {
                throw new RouteGroupException(sprintf('Conflicting route default "%s".', $key));
            }
            $merged[$key] = $value;
        }

        return new self($merged);
    }
}
