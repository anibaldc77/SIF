<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Http;

use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\OAuth2\BearerAuthenticationFailure;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class ResourceServerAuthenticationResult
{
    private function __construct(
        private ?AuthenticatedPrincipal $principal,
        private ?ValidatedAccessToken $token,
        private ?BearerAuthenticationFailure $failure
    ) {
    }

    public static function authenticated(
        AuthenticatedPrincipal $principal,
        ValidatedAccessToken $token
    ): self {
        return new self($principal, $token, null);
    }

    public static function failed(
        BearerAuthenticationFailure $failure
    ): self {
        return new self(null, null, $failure);
    }

    public function isAuthenticated(): bool
    {
        return $this->principal !== null;
    }

    public function principal(): ?AuthenticatedPrincipal
    {
        return $this->principal;
    }

    public function token(): ?ValidatedAccessToken
    {
        return $this->token;
    }

    public function failure(): ?BearerAuthenticationFailure
    {
        return $this->failure;
    }
}
