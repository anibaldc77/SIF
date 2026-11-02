<?php

declare(strict_types=1);

namespace Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;

/**
 * Resolves explicitly provisioned Credential Issuer trust policies.
 *
 * The caller MUST supply policies from trusted Runtime configuration.
 * Never construct this provider from wallet-submitted data.
 *
 * This provider does not validate certificates, authenticate attestation
 * issuers, or produce a positive Key Attestation verification result.
 */
final readonly class OpenId4VcHaipConfiguredTrustPolicyProvider implements OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface
{
    /** @var array<string, OpenId4VcHaipKeyAttestationTrustPolicy> */
    private array $policies;

    /** @param list<OpenId4VcHaipKeyAttestationTrustPolicy> $policies */
    public function __construct(array $policies = [])
    {
        $indexed = [];

        foreach ($policies as $policy) {
            $identifier = $policy->credentialIssuerIdentifier();
            $key = self::indexKey($identifier);

            if (isset($indexed[$key])) {
                throw new InvalidArgumentException(
                    'Duplicate Credential Issuer trust policy.'
                );
            }

            $indexed[$key] = $policy;
        }

        $this->policies = $indexed;
    }

    public function forCredentialIssuer(
        string $credentialIssuerIdentifier
    ): ?OpenId4VcHaipKeyAttestationTrustPolicy {
        if (
            $credentialIssuerIdentifier === ''
            || trim($credentialIssuerIdentifier) !== $credentialIssuerIdentifier
        ) {
            return null;
        }

        return $this->policies[
            self::indexKey($credentialIssuerIdentifier)
        ] ?? null;
    }

    private static function indexKey(string $identifier): string
    {
        return 'credential-issuer:' . $identifier;
    }
}
