<?php

declare(strict_types=1);

namespace Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface;

/**
 * Resolves an explicitly bound and policy-authorized attestation signer.
 *
 * Bindings MUST be provisioned by trusted Runtime configuration.
 * This is NOT a complete Key Attestation verifier.
 *
 * A positive identity result does not prove freshness, key binding,
 * credential proof conformance, or full HAIP compliance.
 */
final readonly class OpenId4VcHaipExplicitCertificateIssuerBindingResolver
{
    /** @var array<string, string> */
    private array $bindings;

    /**
     * @param list<array{
     *     credentialIssuerIdentifier: string,
     *     certificateSha256Fingerprint: string,
     *     attestationIssuerIdentifier: string
     * }> $bindings
     */
    public function __construct(
        private OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface $policyProvider,
        array $bindings
    ) {
        $indexed = [];

        foreach ($bindings as $binding) {
            $credentialIssuer = $binding['credentialIssuerIdentifier'];
            $fingerprint = $binding['certificateSha256Fingerprint'];
            $attestationIssuer = $binding['attestationIssuerIdentifier'];

            if (
                !self::validIdentifier($credentialIssuer)
                || !self::validIdentifier($attestationIssuer)
                || preg_match('/\A[a-f0-9]{64}\z/', $fingerprint) !== 1
            ) {
                throw new InvalidArgumentException(
                    'Invalid attestation certificate issuer binding.'
                );
            }

            $key = self::bindingKey($credentialIssuer, $fingerprint);

            if (isset($indexed[$key])) {
                throw new InvalidArgumentException(
                    'Duplicate attestation certificate issuer binding.'
                );
            }

            $indexed[$key] = $attestationIssuer;
        }

        $this->bindings = $indexed;
    }

    /**
     * Returns only the identity established for the supported certificate
     * binding profile. This does not verify Key Attestation as a whole.
     *
     * Fails closed if revocation or additional EKU controls are required,
     * because those guarantees are not implemented by this increment.
     */
    public function resolveAuthorizedSignerIdentifier(
        string $credentialIssuerIdentifier,
        string $serializedJwt
    ): ?string {
        if (!self::validIdentifier($credentialIssuerIdentifier)) {
            return null;
        }

        $policy = $this->policyProvider->forCredentialIssuer(
            $credentialIssuerIdentifier
        );

        if (
            $policy === null
            || $policy->credentialIssuerIdentifier() !== $credentialIssuerIdentifier
            || $policy->revocationCheckRequired()
            || $policy->requiredExtendedKeyUsageOids() !== []
        ) {
            return null;
        }

        try {
            $evidence = OpenId4VcHaipUnverifiedKeyAttestationJwt::parse(
                $serializedJwt
            );
        } catch (InvalidArgumentException) {
            return null;
        }

        $header = $evidence->unverifiedHeader();

        if (!in_array(
            $header['alg'] ?? null,
            $policy->permittedAlgorithms(),
            true
        )) {
            return null;
        }

        if (
            !(new OpenId4VcHaipUntrustedEs256SignatureMatcher())
                ->matches($evidence)
        ) {
            return null;
        }

        if (
            !(new OpenId4VcHaipExplicitX509TrustEvaluator())
                ->chainMatchesTrustedAnchors(
                    $evidence,
                    $policy->trustedAnchorPems()
                )
        ) {
            return null;
        }

        $certificateDer = $evidence->unverifiedCertificateDerChain()[0];
        $fingerprint = hash('sha256', $certificateDer);

        $key = self::bindingKey(
            $credentialIssuerIdentifier,
            $fingerprint
        );

        $issuer = $this->bindings[$key] ?? null;

        if (
            $issuer === null
            || !in_array(
                $issuer,
                $policy->authorizedAttestationIssuerIdentifiers(),
                true
            )
        ) {
            return null;
        }

        $claims = $evidence->unverifiedClaims();
        $declaredIssuer = $claims['iss'] ?? null;

        if ($declaredIssuer !== null && $declaredIssuer !== $issuer) {
            return null;
        }

        return $issuer;
    }

    private static function bindingKey(
        string $credentialIssuerIdentifier,
        string $fingerprint
    ): string {
        return strlen($credentialIssuerIdentifier)
            . ':'
            . $credentialIssuerIdentifier
            . ':'
            . $fingerprint;
    }

    private static function validIdentifier(string $value): bool
    {
        return $value !== ''
            && trim($value) === $value
            && strlen($value) <= 512
            && !str_contains($value, '*');
    }
}
