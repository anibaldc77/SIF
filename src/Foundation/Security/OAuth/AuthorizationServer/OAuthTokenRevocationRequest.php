<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthTokenRevocationRequest
{
    public function __construct(
        private string $token,
        private ?string $tokenTypeHint = null
    ) {
    }

    public function token(): string
    {
        return $this->token;
    }

    public function tokenTypeHint(): ?string
    {
        return $this->tokenTypeHint;
    }
}
