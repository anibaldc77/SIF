<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Exceptions\InvalidConstructorBindingException;

final readonly class ConstructorArgumentBindings
{
    /**
     * @var array<string, ConstructorArgumentBinding>
     */
    private array $bindings;

    /**
     * @param array<string, ConstructorArgumentBinding> $bindings
     */
    public function __construct(array $bindings = [])
    {
        $normalized = [];

        foreach ($bindings as $parameter => $binding) {
            if (trim($parameter) === '') {
                throw new InvalidConstructorBindingException(
                    'Constructor parameter name cannot be empty.',
                );
            }

            $normalized[$parameter] = $binding;
        }

        ksort($normalized, SORT_STRING);

        $this->bindings = $normalized;
    }

    public function has(string $parameter): bool
    {
        return isset($this->bindings[$parameter]);
    }

    public function get(string $parameter): ConstructorArgumentBinding
    {
        if (!$this->has($parameter)) {
            throw new InvalidConstructorBindingException(
                sprintf(
                    'No constructor binding exists for parameter "%s".',
                    $parameter,
                ),
            );
        }

        return $this->bindings[$parameter];
    }

    /**
     * @return array<string, ConstructorArgumentBinding>
     */
    public function all(): array
    {
        return $this->bindings;
    }

    public function isEmpty(): bool
    {
        return $this->bindings === [];
    }
}
