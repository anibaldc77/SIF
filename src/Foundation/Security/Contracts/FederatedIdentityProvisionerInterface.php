<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\Federation\LinkedLocalIdentity;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

interface FederatedIdentityProvisionerInterface
{
    public function provision(
        OidcFederatedIdentity $federatedIdentity
    ): LinkedLocalIdentity;
}
