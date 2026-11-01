<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;

/**
 * Immutable requirements from the Credential Issuer's TRUSTED configuration.
 *
 * This object does not verify certificates, signatures, revocation, temporal
 * claims or attestation issuers. Never construct it from wallet-supplied data.
 * A resolver must fail closed when no trusted policy is configured.
 */
final readonly class OpenId4VcHaipKeyAttestationTrustPolicy
{
    /** @var non-empty-list<string> */
    private array $trustedAnchorPems;

    /** @var non-empty-list<string> */
    private array $authorizedAttestationIssuerIdentifiers;

    /** @var non-empty-list<string> */
    private array $permittedAlgorithms;

    /** @var list<string> */
    private array $requiredExtendedKeyUsageOids;

    /**
     * @param list<string> $trustedAnchorPems PEM-encoded certificates supplied by trusted configuration
     * @param list<string> $authorizedAttestationIssuerIdentifiers Verified attestation issuer identities to allow
     * @param list<string> $permittedAlgorithms Supported JWS algorithms explicitly allowed by the issuer
     * @param list<string> $requiredExtendedKeyUsageOids Additional ecosystem EKU OIDs; empty means no additional EKU restriction
     */
    public function __construct(
        private string $credentialIssuerIdentifier,
        array $trustedAnchorPems,
        array $authorizedAttestationIssuerIdentifiers,
        array $permittedAlgorithms,
        array $requiredExtendedKeyUsageOids = [],
        private bool $revocationCheckRequired = true,
        private int $maximumAttestationAgeSeconds = 300,
        private int $allowedClockSkewSeconds = 0
    ) {
        self::validateIdentifier($credentialIssuerIdentifier, 'Credential Issuer');
        self::validateList($trustedAnchorPems, 'Trust anchors', 16, false);
        self::validateList($authorizedAttestationIssuerIdentifiers, 'Attestation issuers', 64, false);
        self::validateList($permittedAlgorithms, 'Algorithms', 8, false);
        self::validateList($requiredExtendedKeyUsageOids, 'Extended Key Usage', 16, true);

        // validateList() checks these invariants at runtime, but its void return
        // does not refine the argument types for PHPStan. This explicit guard
        // also preserves the required fail-closed non-empty list contract.
        if (
            $trustedAnchorPems === []
            || $authorizedAttestationIssuerIdentifiers === []
            || $permittedAlgorithms === []
        ) {
            throw new InvalidArgumentException('Required trust policy lists cannot be empty.');
        }

        $anchorFingerprints = [];
        foreach ($trustedAnchorPems as $pem) {
            $der = self::canonicalPemDer($pem);
            if ($der === null) {
                throw new InvalidArgumentException('Invalid trusted anchor PEM encoding.');
            }
            $fingerprint = hash('sha256', $der);
            if (isset($anchorFingerprints[$fingerprint])) {
                throw new InvalidArgumentException('Duplicate trusted anchor certificate.');
            }
            $anchorFingerprints[$fingerprint] = true;
        }

        self::validateUniqueIdentifiers($authorizedAttestationIssuerIdentifiers, 'Attestation issuer');

        foreach ($permittedAlgorithms as $algorithm) {
            // The current dedicated adapter verifies ES256 only. Other algorithms
            // require explicit new adapter support and a policy revision.
            if ($algorithm !== 'ES256') {
                throw new InvalidArgumentException('Unsupported attestation algorithm policy.');
            }
        }
        if (count(array_unique($permittedAlgorithms)) !== count($permittedAlgorithms)) {
            throw new InvalidArgumentException('Duplicate attestation algorithm.');
        }

        foreach ($requiredExtendedKeyUsageOids as $oid) {
            if (preg_match('/\A(?:0|1|2)\.(?:0|[1-9][0-9]*)(?:\.(?:0|[1-9][0-9]*))+\z/D', $oid) !== 1) {
                throw new InvalidArgumentException('Invalid Extended Key Usage OID.');
            }
        }
        if (count(array_unique($requiredExtendedKeyUsageOids)) !== count($requiredExtendedKeyUsageOids)) {
            throw new InvalidArgumentException('Duplicate Extended Key Usage OID.');
        }
        if ($maximumAttestationAgeSeconds < 1 || $maximumAttestationAgeSeconds > 86400) {
            throw new InvalidArgumentException('Invalid maximum attestation age.');
        }
        if ($allowedClockSkewSeconds < 0 || $allowedClockSkewSeconds > 300) {
            throw new InvalidArgumentException('Invalid clock skew allowance.');
        }

        $this->trustedAnchorPems = $trustedAnchorPems;
        $this->authorizedAttestationIssuerIdentifiers = $authorizedAttestationIssuerIdentifiers;
        $this->permittedAlgorithms = $permittedAlgorithms;
        $this->requiredExtendedKeyUsageOids = $requiredExtendedKeyUsageOids;
    }

    public function credentialIssuerIdentifier(): string
    {
        return $this->credentialIssuerIdentifier;
    }

    /** @return non-empty-list<string> */
    public function trustedAnchorPems(): array
    {
        return $this->trustedAnchorPems;
    }

    /** @return non-empty-list<string> */
    public function authorizedAttestationIssuerIdentifiers(): array
    {
        return $this->authorizedAttestationIssuerIdentifiers;
    }

    /** @return non-empty-list<string> */
    public function permittedAlgorithms(): array
    {
        return $this->permittedAlgorithms;
    }

    /** @return list<string> */
    public function requiredExtendedKeyUsageOids(): array
    {
        return $this->requiredExtendedKeyUsageOids;
    }

    public function digitalSignatureKeyUsageRequired(): bool
    {
        return true;
    }

    public function certificateValidityRequired(): bool
    {
        return true;
    }

    public function revocationCheckRequired(): bool
    {
        return $this->revocationCheckRequired;
    }

    public function maximumAttestationAgeSeconds(): int
    {
        return $this->maximumAttestationAgeSeconds;
    }

    public function allowedClockSkewSeconds(): int
    {
        return $this->allowedClockSkewSeconds;
    }

    /** @param list<string> $values */
    private static function validateList(array $values, string $name, int $maximum, bool $allowEmpty): void
    {
        if (!array_is_list($values) || (!$allowEmpty && $values === []) || count($values) > $maximum) {
            throw new InvalidArgumentException('Invalid ' . $name . ' configuration.');
        }
        foreach ($values as $value) {
            if ($value === '' || strlen($value) > 32768) {
                throw new InvalidArgumentException('Invalid ' . $name . ' entry.');
            }
        }
    }

    private static function validateIdentifier(string $identifier, string $name): void
    {
        if ($identifier === '' || trim($identifier) !== $identifier || strlen($identifier) > 512) {
            throw new InvalidArgumentException('Invalid ' . $name . ' identifier.');
        }
    }

    /** @param list<string> $identifiers */
    private static function validateUniqueIdentifiers(array $identifiers, string $name): void
    {
        $seen = [];
        foreach ($identifiers as $identifier) {
            self::validateIdentifier($identifier, $name);
            if (isset($seen[$identifier])) {
                throw new InvalidArgumentException('Duplicate ' . $name . ' identifier.');
            }
            $seen[$identifier] = true;
        }
    }

    /** Structural PEM encoding only; this is NOT X.509 certificate validation. */
    private static function canonicalPemDer(string $pem): ?string
    {
        if (strlen($pem) > 32768 || preg_match(
            '/\A-----BEGIN CERTIFICATE-----\s+([A-Za-z0-9+\/=\r\n]+)-----END CERTIFICATE-----\s*\z/D',
            $pem,
            $matches
        ) !== 1) {
            return null;
        }
        $encoded = preg_replace('/\s+/', '', $matches[1]);
        if (!is_string($encoded) || $encoded === '') {
            return null;
        }
        $der = base64_decode($encoded, true);
        return $der !== false && $der !== '' && base64_encode($der) === $encoded ? $der : null;
    }
}
