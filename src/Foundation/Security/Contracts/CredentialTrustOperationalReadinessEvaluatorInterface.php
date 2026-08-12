<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement\CredentialTrustOperationalReadinessReport;

interface CredentialTrustOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        CredentialTrustProfile $profile
    ): CredentialTrustOperationalReadinessReport;
}
