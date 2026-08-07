<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwks;

use InvalidArgumentException;

final readonly class Jwk
{
    /**
     * @param array<string, scalar|null> $parameters
     */
    public function __construct(
        private string $keyId,
        private string $keyType,
        private ?string $algorithm,
        private array $parameters
    ) {
        if ($this->keyId === '' || strlen($this->keyId) > 512) {
            throw new InvalidArgumentException('JWK key identifier is invalid.');
        }

        if ($this->keyType === '' || strlen($this->keyType) > 32) {
            throw new InvalidArgumentException('JWK key type is invalid.');
        }
    }

    public function keyId(): string
    {
        return $this->keyId;
    }

    public function keyType(): string
    {
        return $this->keyType;
    }

    public function algorithm(): ?string
    {
        return $this->algorithm;
    }

    /** @return array<string, scalar|null> */
    public function parameters(): array
    {
        return $this->parameters;
    }
}
