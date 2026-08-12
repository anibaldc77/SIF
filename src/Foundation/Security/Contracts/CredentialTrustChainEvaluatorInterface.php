<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainContext;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialTrustChainEvaluatorInterface
{
    public function evaluate(
        CredentialTrustEntityReference $entity,
        CredentialTrustProfile $profile,
        CredentialTrustChainContext $context
    ): CredentialTrustChainAssessment;
}
