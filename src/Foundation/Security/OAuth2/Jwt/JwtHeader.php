<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwt;

use InvalidArgumentException;

final readonly class JwtHeader
{
    public function __construct(
        private string $algorithm,
        private ?string $keyId = null,
        private ?string $type = null
    ) {
        if ($this->algorithm === '' || strlen($this->algorithm) > 80) {
            throw new InvalidArgumentException('JWT algorithm is invalid.');
        }

        if ($this->keyId !== null && strlen($this->keyId) > 512) {
            throw new InvalidArgumentException('JWT key identifier is too long.');
        }

        if ($this->type !== null && strlen($this->type) > 80) {
            throw new InvalidArgumentException('JWT type is too long.');
        }
    }

    public function algorithm(): string
    {
        return $this->algorithm;
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    public function type(): ?string
    {
        return $this->type;
    }
}
