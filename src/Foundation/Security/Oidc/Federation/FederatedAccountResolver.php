<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use Sif\Foundation\Security\Contracts\FederatedIdentityLinkResolverInterface;
use Sif\Foundation\Security\Contracts\FederatedIdentityProvisionerInterface;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final readonly class FederatedAccountResolver
{
    public function __construct(
        private FederatedIdentityLinkResolverInterface $linkResolver,
        private FederatedIdentityProvisionerInterface $provisioner,
        private FederatedProvisioningPolicy $policy
    ) {
    }

    public function resolve(
        OidcFederatedIdentity $federatedIdentity
    ): ?LinkedLocalIdentity {
        $linked = $this->linkResolver->resolve($federatedIdentity);

        if ($linked !== null) {
            return $linked;
        }

        if (!$this->policy->allowsAutomaticProvisioning()) {
            return null;
        }

        return $this->provisioner->provision($federatedIdentity);
    }
}
