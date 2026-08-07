<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use DateTimeImmutable;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;

final readonly class FederatedAuthenticationMapper
{
    public function __construct(
        private FederatedAccountResolver $accountResolver,
        private FederatedPrincipalFactory $principalFactory
    ) {
    }

    public function map(
        OidcFederatedIdentity $federatedIdentity,
        DateTimeImmutable $authenticatedAt
    ): ?FederatedAuthenticationMappingResult {
        $linkedIdentity = $this->accountResolver->resolve(
            $federatedIdentity
        );

        if ($linkedIdentity === null) {
            return null;
        }

        return new FederatedAuthenticationMappingResult(
            $federatedIdentity,
            $linkedIdentity,
            $this->principalFactory->create(
                $linkedIdentity,
                $federatedIdentity,
                $authenticatedAt
            )
        );
    }
}
