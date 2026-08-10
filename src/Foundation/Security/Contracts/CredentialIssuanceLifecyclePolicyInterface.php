<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceLifecycleAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceLifecycleStatus;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceTransaction;

interface CredentialIssuanceLifecyclePolicyInterface
{
    public function assess(
        CredentialIssuanceTransaction $transaction,
        CredentialIssuanceLifecycleStatus $status
    ): CredentialIssuanceLifecycleAssessment;
}
