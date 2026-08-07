<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlAssertion;
use Sif\Foundation\Security\Saml\SamlAuthenticatedIdentity;

interface SamlIdentityMapperInterface
{
    /**
     * @param array<string, scalar|list<scalar>|null> $attributes
     */
    public function map(
        SamlAssertion $assertion,
        array $attributes = []
    ): SamlAuthenticatedIdentity;
}
