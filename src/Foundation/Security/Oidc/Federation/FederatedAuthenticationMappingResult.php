<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final readonly class FederatedAuthenticationMappingResult
{
    public function __construct(
        private OidcFederatedIdentity $federatedIdentity,
        private LinkedLocalIdentity $linkedIdentity,
        private AuthenticatedPrincipal $principal
    ) {
    }

    public function federatedIdentity(): OidcFederatedIdentity
    {
        return $this->federatedIdentity;
    }

    public function linkedIdentity(): LinkedLocalIdentity
    {
        return $this->linkedIdentity;
    }

    public function principal(): AuthenticatedPrincipal
    {
        return $this->principal;
    }
}
