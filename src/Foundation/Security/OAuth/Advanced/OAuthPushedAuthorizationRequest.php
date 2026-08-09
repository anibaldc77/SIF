<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;

final readonly class OAuthPushedAuthorizationRequest
{
    public function __construct(
        private OAuthPushedAuthorizationRequestUri $requestUri,
        private OAuthAuthorizationRequest $authorizationRequest,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt
    ) {
        if ($this->expiresAt <= $this->issuedAt) {
            throw new InvalidArgumentException(
                'OAuth pushed authorization request expiration is invalid.'
            );
        }
    }

    public function requestUri(): OAuthPushedAuthorizationRequestUri
    {
        return $this->requestUri;
    }

    public function authorizationRequest(): OAuthAuthorizationRequest
    {
        return $this->authorizationRequest;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
