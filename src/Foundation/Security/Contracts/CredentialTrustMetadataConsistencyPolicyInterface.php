<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata\CredentialTrustMetadataConsistencyResult;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata\CredentialTrustMetadataSnapshot;

interface CredentialTrustMetadataConsistencyPolicyInterface
{
    public function compare(
        CredentialTrustMetadataSnapshot $previous,
        CredentialTrustMetadataSnapshot $current
    ): CredentialTrustMetadataConsistencyResult;
}
