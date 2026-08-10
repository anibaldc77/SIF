<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceAssessment;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceContext;
use Sif\Foundation\Security\VerifiableCredentials\VerifiedIdentityClaims;

interface IdentityAssurancePolicyInterface
{
    public function assess(
        VerifiedIdentityClaims $claims,
        IdentityAssuranceContext $context
    ): IdentityAssuranceAssessment;
}
