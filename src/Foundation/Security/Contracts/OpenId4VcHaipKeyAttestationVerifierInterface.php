<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestation;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult;

interface OpenId4VcHaipKeyAttestationVerifierInterface
{
    /**
     * Verify serializedJwt() using a trusted cryptographic and certificate
     * policy. A SHA-256 fingerprint and caller-declared key references are
     * NEVER sufficient to return a valid result.
     *
     * $issuerIdentifier identifies the Credential Issuer whose configured
     * trust policy applies. It is NOT necessarily the attestation JWT issuer
     * (the latter may be a Wallet Provider or a key-storage component).
     *
     * Implementations must return invalid if the raw JWT, trusted issuer
     * roots, permitted algorithms, or other required checks are unavailable.
     */    public function verify(
        OpenId4VcHaipKeyAttestation $attestation,
        string $issuerIdentifier
    ): OpenId4VcHaipKeyAttestationVerificationResult;
}
