<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofRequirements;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration;

interface OpenId4VcHaipCredentialProofRequirementsResolverInterface
{
    public function resolve(
        CredentialConfiguration $configuration,
        string $verifiedProofType
    ): OpenId4VcHaipCredentialProofRequirements;
}