<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthSignedAccessToken
{
    public function __construct(
        private string $value,
        private string $keyId,
        private string $algorithm,
        private OAuthJwtClaims $claims
    ) {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function keyId(): string
    {
        return $this->keyId;
    }

    public function algorithm(): string
    {
        return $this->algorithm;
    }

    public function claims(): OAuthJwtClaims
    {
        return $this->claims;
    }
}
