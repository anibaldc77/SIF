<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

final readonly class OidcTokenExchangeRequestFactory
{
    public function create(
        OidcAuthorizationTransaction $transaction,
        OidcAuthorizationCallback $callback,
        OidcClientRegistration $registration,
        ?OidcClientSecret $clientSecret = null
    ): OidcTokenExchangeRequest {
        return new OidcTokenExchangeRequest(
            $callback->code(),
            $registration,
            $transaction->codeVerifier(),
            $clientSecret
        );
    }
}
