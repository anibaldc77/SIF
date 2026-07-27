<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Exceptions\DuplicateContextualBindingException;

final class ContextualBindingRegistry
{
    /**
     * @var array<string, ContextualBinding>
     */
    private array $bindings = [];

    public function register(ContextualBinding $binding): void
    {
        $key = $binding->key();

        if (isset($this->bindings[$key])) {
            throw new DuplicateContextualBindingException(
                sprintf(
                    'Contextual binding "%s" is already registered.',
                    $key,
                ),
            );
        }

        $this->bindings[$key] = $binding;
    }

    public function has(
        ServiceIdentifier $consumer,
        string $parameterName,
    ): bool {
        return isset(
            $this->bindings[$consumer->value() . '::' . $parameterName],
        );
    }

    public function get(
        ServiceIdentifier $consumer,
        string $parameterName,
    ): ContextualBinding {
        $key = $consumer->value() . '::' . $parameterName;

        if (!isset($this->bindings[$key])) {
            throw new \Sif\Foundation\Exceptions\ContextualBindingNotFoundException(
                sprintf(
                    'Contextual binding "%s" is not registered.',
                    $key,
                ),
            );
        }

        return $this->bindings[$key];
    }

    /**
     * @return list<ContextualBinding>
     */
    public function all(): array
    {
        return array_values($this->bindings);
    }
}
