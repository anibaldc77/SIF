<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

final readonly class OpenId4VcHaipAuthorizationContext
{
    public function __construct(
        private bool $authorizationCodeSupported,
        private bool $authorizationEndpointUsed,
        private bool $parUsed,
        private ?string $pkceCodeChallengeMethod,
        private bool $authorizationResponseIssuerValidated,
        private bool $dpopSupported,
        private bool $dpopNonceSupported,
        private bool $scopeMapsToCredentialType
    ) {
    }

    public function authorizationCodeSupported(): bool
    {
        return $this->authorizationCodeSupported;
    }

    public function authorizationEndpointUsed(): bool
    {
        return $this->authorizationEndpointUsed;
    }

    public function parUsed(): bool
    {
        return $this->parUsed;
    }

    public function pkceCodeChallengeMethod(): ?string
    {
        return $this->pkceCodeChallengeMethod;
    }

    public function authorizationResponseIssuerValidated(): bool
    {
        return $this->authorizationResponseIssuerValidated;
    }

    public function dpopSupported(): bool
    {
        return $this->dpopSupported;
    }

    public function dpopNonceSupported(): bool
    {
        return $this->dpopNonceSupported;
    }

    public function scopeMapsToCredentialType(): bool
    {
        return $this->scopeMapsToCredentialType;
    }
}
