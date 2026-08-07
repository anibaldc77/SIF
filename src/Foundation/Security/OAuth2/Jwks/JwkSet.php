<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwks;

use InvalidArgumentException;

final readonly class JwkSet
{
    /** @var array<string, Jwk> */
    private array $keys;

    /**
     * @param iterable<Jwk> $keys
     */
    public function __construct(iterable $keys)
    {
        $normalized = [];

        foreach ($keys as $key) {
            $id = $key->keyId();

            if (isset($normalized[$id])) {
                throw new InvalidArgumentException(
                    sprintf('Duplicate JWK key identifier "%s".', $id)
                );
            }

            $normalized[$id] = $key;
        }

        ksort($normalized);
        $this->keys = $normalized;
    }

    public function find(string $keyId): ?Jwk
    {
        return $this->keys[$keyId] ?? null;
    }

    public function count(): int
    {
        return count($this->keys);
    }
}
