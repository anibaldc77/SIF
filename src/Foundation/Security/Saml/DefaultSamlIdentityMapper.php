<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use Sif\Foundation\Security\Contracts\SamlIdentityMapperInterface;

final readonly class DefaultSamlIdentityMapper implements SamlIdentityMapperInterface
{
    /**
     * @param array<string, scalar|list<scalar>|null> $attributes
     */

    public function map(
        SamlAssertion $assertion,
        array $attributes = []
    ): SamlAuthenticatedIdentity {
        return new SamlAuthenticatedIdentity(
            $assertion->subject()->value(),
            $assertion->issuer(),
            $attributes
        );
    }
}
