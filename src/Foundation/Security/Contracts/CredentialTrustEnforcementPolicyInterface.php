<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement\CredentialTrustEnforcementDecision;

interface CredentialTrustEnforcementPolicyInterface
{
    public function enforce(
        CredentialTrustDecision $decision
    ): CredentialTrustEnforcementDecision;
}
