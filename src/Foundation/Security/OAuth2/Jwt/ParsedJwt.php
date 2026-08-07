<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwt;

final readonly class ParsedJwt
{
    public function __construct(
        private JwtHeader $header,
        private JwtClaims $claims,
        private string $signingInput,
        private string $signature
    ) {
    }

    public function header(): JwtHeader
    {
        return $this->header;
    }

    public function claims(): JwtClaims
    {
        return $this->claims;
    }

    public function signingInput(): string
    {
        return $this->signingInput;
    }

    public function signature(): string
    {
        return $this->signature;
    }
}
