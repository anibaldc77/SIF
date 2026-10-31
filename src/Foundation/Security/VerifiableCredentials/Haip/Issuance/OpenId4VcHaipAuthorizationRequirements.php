<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

final readonly class OpenId4VcHaipAuthorizationRequirements
{
    public function authorizationCodeRequired(): bool
    {
        return true;
    }

    public function pkceRequired(): bool
    {
        return true;
    }

    public function pkceCodeChallengeMethod(): string
    {
        return 'S256';
    }

    public function parRequiredWhenAuthorizationEndpointUsed(): bool
    {
        return true;
    }

    public function authorizationResponseIssuerRequired(): bool
    {
        return true;
    }

    public function dpopRequired(): bool
    {
        return true;
    }

    public function dpopNonceSupportRequired(): bool
    {
        return true;
    }

    public function scopeCredentialTypeBindingRequired(): bool
    {
        return true;
    }

    /** @return array<string, bool|string> */
    public function toArray(): array
    {
        return [
            'authorization_code_required' => $this->authorizationCodeRequired(),
            'pkce_required' => $this->pkceRequired(),
            'pkce_code_challenge_method' => $this->pkceCodeChallengeMethod(),
            'par_required_when_authorization_endpoint_used'
                => $this->parRequiredWhenAuthorizationEndpointUsed(),
            'authorization_response_issuer_required'
                => $this->authorizationResponseIssuerRequired(),
            'dpop_required' => $this->dpopRequired(),
            'dpop_nonce_support_required' => $this->dpopNonceSupportRequired(),
            'scope_credential_type_binding_required'
                => $this->scopeCredentialTypeBindingRequired(),
        ];
    }
}
