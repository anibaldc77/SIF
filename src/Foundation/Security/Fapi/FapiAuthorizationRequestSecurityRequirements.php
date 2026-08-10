<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiAuthorizationRequestSecurityRequirements
{
    public function __construct(
        private bool $requireAuthorizationCodeResponseType = true,
        private bool $requirePar = true,
        private bool $requireClientAuthenticatedPar = true,
        private bool $requirePkceS256 = true,
        private bool $requireRedirectUriInPar = true,
        private bool $requireIssuerParameterValidation = true,
        private int $maximumParLifetimeSeconds = 599
    ) {
    }

    public function requireAuthorizationCodeResponseType(): bool
    {
        return $this->requireAuthorizationCodeResponseType;
    }

    public function requirePar(): bool
    {
        return $this->requirePar;
    }

    public function requireClientAuthenticatedPar(): bool
    {
        return $this->requireClientAuthenticatedPar;
    }

    public function requirePkceS256(): bool
    {
        return $this->requirePkceS256;
    }

    public function requireRedirectUriInPar(): bool
    {
        return $this->requireRedirectUriInPar;
    }

    public function requireIssuerParameterValidation(): bool
    {
        return $this->requireIssuerParameterValidation;
    }

    public function maximumParLifetimeSeconds(): int
    {
        return $this->maximumParLifetimeSeconds;
    }
}
