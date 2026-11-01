<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;

interface OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface
{
    /**
     * Resolve an issuer-owned policy from trusted configuration, NEVER from
     * wallet-submitted JWT claims, JOSE headers or caller-supplied flags.
     * Return null when unavailable or unauthorized; callers MUST fail closed.
     */
    public function forCredentialIssuer(string $credentialIssuerIdentifier): ?OpenId4VcHaipKeyAttestationTrustPolicy;
}
