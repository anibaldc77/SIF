<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Product\CredentialTrustProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Product\CredentialTrustProductReadinessReport;

interface CredentialTrustProductReadinessEvaluatorInterface
{
    public function evaluate(
        CredentialTrustProductProfile $profile
    ): CredentialTrustProductReadinessReport;
}
