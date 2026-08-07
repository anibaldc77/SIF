<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final readonly class FederatedLoginResult
{
    public function __construct(
        private AuthenticatedPrincipal $principal,
        private OidcFederatedIdentity $federatedIdentity
    ) {
    }

    public function principal(): AuthenticatedPrincipal
    {
        return $this->principal;
    }

    public function federatedIdentity(): OidcFederatedIdentity
    {
        return $this->federatedIdentity;
    }
}
