<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final readonly class FederatedRevocationRequest
{
    public function __construct(
        private IdentityId $localIdentityId,
        private OidcFederatedIdentity $federatedIdentity,
        private FederatedRevocationScope $scope,
        private FederatedRevocationReason $reason
    ) {
    }

    public function localIdentityId(): IdentityId
    {
        return $this->localIdentityId;
    }

    public function federatedIdentity(): OidcFederatedIdentity
    {
        return $this->federatedIdentity;
    }

    public function scope(): FederatedRevocationScope
    {
        return $this->scope;
    }

    public function reason(): FederatedRevocationReason
    {
        return $this->reason;
    }
}
