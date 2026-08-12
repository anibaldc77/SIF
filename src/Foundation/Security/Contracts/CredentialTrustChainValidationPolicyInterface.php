<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChain;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChainValidationResult;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainContext;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialTrustChainValidationPolicyInterface
{
    public function validate(
        CredentialTrustChain $chain,
        CredentialTrustProfile $profile,
        CredentialTrustChainContext $context
    ): CredentialTrustChainValidationResult;
}
