<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthSigningKey
{
    public function __construct(
        private string $keyId,
        private string $algorithm,
        private string $materialReference
    ) {
        if (
            trim($this->keyId) === ''
            || trim($this->algorithm) === ''
            || trim($this->materialReference) === ''
        ) {
            throw new InvalidArgumentException(
                'OAuth signing key is invalid.'
            );
        }
    }

    public function keyId(): string
    {
        return $this->keyId;
    }

    public function algorithm(): string
    {
        return $this->algorithm;
    }

    public function materialReference(): string
    {
        return $this->materialReference;
    }
}
