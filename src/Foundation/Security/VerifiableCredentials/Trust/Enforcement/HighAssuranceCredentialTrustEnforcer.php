<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement;

use Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface;
use Sif\Foundation\Security\Exceptions\CredentialTrustEnforcementException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision;

final readonly class HighAssuranceCredentialTrustEnforcer implements CredentialTrustEnforcementPolicyInterface
{
    public function enforce(
        CredentialTrustDecision $decision
    ): CredentialTrustEnforcementDecision {
        if (!$decision->trusted()) {
            throw new CredentialTrustEnforcementException(
                'Credential trust decision is not trusted.'
            );
        }

        if ($decision->stale()) {
            throw new CredentialTrustEnforcementException(
                'High-assurance trust enforcement rejects stale evidence.'
            );
        }

        if ($decision->refreshRecommended()) {
            throw new CredentialTrustEnforcementException(
                'High-assurance trust enforcement requires current evidence.'
            );
        }

        return new CredentialTrustEnforcementDecision(
            $decision,
            true
        );
    }
}
