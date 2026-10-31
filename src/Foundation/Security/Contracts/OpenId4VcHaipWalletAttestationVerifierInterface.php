<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\OpenId4VcHaipWalletAttestation;

interface OpenId4VcHaipWalletAttestationVerifierInterface
{
    public function verify(
        OpenId4VcHaipWalletAttestation $attestation
    ): void;
}
