<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Verifier;

use Sif\Foundation\Security\Contracts\CredentialStatusFreshnessPolicyInterface;
use Sif\Foundation\Security\Exceptions\CredentialStatusEnforcementException;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusAssessment;

final readonly class HighAssuranceCredentialStatusEnforcer
{
    public function __construct(
        private CredentialStatusFreshnessPolicyInterface $freshnessPolicy
    ) {
    }

    public function enforce(
        CredentialStatusAssessment $assessment
    ): CredentialStatusEnforcementDecision {
        $this->freshnessPolicy->validate($assessment);

        if ($assessment->revoked()) {
            throw new CredentialStatusEnforcementException(
                'Credential is revoked.'
            );
        }

        if ($assessment->suspended()) {
            throw new CredentialStatusEnforcementException(
                'Credential is suspended.'
            );
        }

        if ($assessment->violations() !== []) {
            throw new CredentialStatusEnforcementException(
                'Credential status assessment contains policy violations.'
            );
        }

        if (!$assessment->valid()) {
            throw new CredentialStatusEnforcementException(
                'Credential status assessment is not valid.'
            );
        }

        return new CredentialStatusEnforcementDecision($assessment);
    }
}