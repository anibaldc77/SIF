<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;

interface CredentialProofFingerprintResolverInterface
{
    public function fingerprint(
        CredentialIssuanceProof $proof
    ): string;
}
