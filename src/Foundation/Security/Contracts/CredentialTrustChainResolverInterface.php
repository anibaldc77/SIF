<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChain;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainContext;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialTrustChainResolverInterface
{
    public function resolve(
        CredentialTrustEntityReference $entity,
        CredentialTrustProfile $profile,
        CredentialTrustChainContext $context
    ): CredentialTrustChain;
}
