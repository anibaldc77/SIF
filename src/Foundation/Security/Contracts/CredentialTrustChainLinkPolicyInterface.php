<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChainLink;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainContext;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustProfile;

interface CredentialTrustChainLinkPolicyInterface
{
    public function validate(
        CredentialTrustChainLink $link,
        CredentialTrustProfile $profile,
        CredentialTrustChainContext $context
    ): void;
}
