<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

/**
 * Structural, UNVERIFIED Key Attestation JWT evidence.
 *
 * Implementations expose untrusted data only.
 * No method establishes signature validity, X.509 trust,
 * attestation issuer authorization, freshness or key binding.
 *
 * Consumers MUST NOT treat these values as authenticated claims.
 */
interface OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface
{
    public function signingInput(): string;

    public function rawSignature(): string;

    /** @return array<string, mixed> */
    public function unverifiedHeader(): array;

    /** @return array<string, mixed> */
    public function unverifiedClaims(): array;

    /** @return non-empty-list<string> */
    public function unverifiedCertificateDerChain(): array;
}
