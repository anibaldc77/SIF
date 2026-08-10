<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\VerifiedIdentityClaims;
use Sif\Foundation\Security\VerifiableCredentials\VerifiablePresentation;

interface IdentityAssuranceClaimsMapperInterface
{
    public function map(
        VerifiablePresentation $presentation
    ): VerifiedIdentityClaims;
}
