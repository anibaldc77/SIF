<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\Federation\LinkedLocalIdentity;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

interface FederatedIdentityLinkResolverInterface
{
    public function resolve(
        OidcFederatedIdentity $federatedIdentity
    ): ?LinkedLocalIdentity;
}
