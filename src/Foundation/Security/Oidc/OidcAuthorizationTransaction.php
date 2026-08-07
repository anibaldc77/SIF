<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

final readonly class OidcAuthorizationTransaction
{
    public function __construct(
        private OidcState $state,
        private OidcNonce $nonce,
        private PkceCodeVerifier $codeVerifier,
        private OidcAuthorizationRequest $request
    ) {
    }

    public function state(): OidcState
    {
        return $this->state;
    }

    public function nonce(): OidcNonce
    {
        return $this->nonce;
    }

    public function codeVerifier(): PkceCodeVerifier
    {
        return $this->codeVerifier;
    }

    public function request(): OidcAuthorizationRequest
    {
        return $this->request;
    }
}
