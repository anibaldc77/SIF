<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;

final readonly class OAuthAuthorizationRequestObject
{
    public function __construct(
        private string $serialized,
        private OAuthAuthorizationRequest $authorizationRequest,
        private string $issuer,
        private string $audience,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private string $tokenId,
        private ?string $keyId = null
    ) {
        if (
            trim($this->serialized) === ''
            || trim($this->issuer) === ''
            || trim($this->audience) === ''
            || trim($this->tokenId) === ''
            || $this->expiresAt <= $this->issuedAt
        ) {
            throw new InvalidArgumentException(
                'OAuth authorization request object is invalid.'
            );
        }
    }

    public function serialized(): string
    {
        return $this->serialized;
    }

    public function authorizationRequest(): OAuthAuthorizationRequest
    {
        return $this->authorizationRequest;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
