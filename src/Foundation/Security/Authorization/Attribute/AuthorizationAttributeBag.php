<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Attribute;

use InvalidArgumentException;

final readonly class AuthorizationAttributeBag
{
    /** @var array<string, scalar|null> */
    private array $attributes;

    /**
     * @param array<string, scalar|null> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $normalized = [];

        foreach ($attributes as $name => $value) {
            $key = strtolower(trim($name));

            if (
                strlen($key) < 1
                || strlen($key) > 120
                || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $key) !== 1
            ) {
                throw new InvalidArgumentException(
                    'Authorization attribute names must use canonical characters.'
                );
            }

            $normalized[$key] = $value;
        }

        ksort($normalized);

        $this->attributes = $normalized;
    }

    public function has(string $name): bool
    {
        return array_key_exists(strtolower(trim($name)), $this->attributes);
    }

    public function get(string $name): string|int|float|bool|null
    {
        return $this->attributes[strtolower(trim($name))] ?? null;
    }

    /** @return array<string, scalar|null> */
    public function all(): array
    {
        return $this->attributes;
    }
}
