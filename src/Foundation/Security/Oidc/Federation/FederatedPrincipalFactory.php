<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\PrincipalAttribute;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final readonly class FederatedPrincipalFactory
{
    public function create(
        LinkedLocalIdentity $linkedIdentity,
        OidcFederatedIdentity $federatedIdentity,
        DateTimeImmutable $authenticatedAt
    ): AuthenticatedPrincipal {
        return new AuthenticatedPrincipal(
            new Identity($linkedIdentity->identityId()),
            new PrincipalAttributeCollection(
                new PrincipalAttribute(
                    'federation.issuer',
                    $federatedIdentity->issuer()
                ),
                new PrincipalAttribute(
                    'federation.subject',
                    $federatedIdentity->subject()
                ),
                new PrincipalAttribute(
                    'federation.stable_key',
                    $federatedIdentity->stableKey()
                )
            ),
            new AuthenticationEvidence(
                new AuthenticationMethod('oidc-federated'),
                new AuthenticationLevel(60),
                $authenticatedAt
            )
        );
    }
}
