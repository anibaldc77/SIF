<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;

interface FederatedIdentityLinkRevokerInterface
{
    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): bool;
}
