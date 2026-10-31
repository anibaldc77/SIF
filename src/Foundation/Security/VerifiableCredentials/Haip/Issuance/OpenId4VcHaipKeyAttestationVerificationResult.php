<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;

final readonly class OpenId4VcHaipKeyAttestationVerificationResult
{
    /** @var list<string> */
    private array $verifiedKeyReferences;

    /** @var list<string> */
    private array $violations;

    /**
     * @param list<string> $verifiedKeyReferences
     * @param list<string> $violations
     */
    public function __construct(
        private bool $valid,
        array $verifiedKeyReferences,
        array $violations = [],
        private ?string $verifiedIssuerIdentifier = null,
        private ?OpenId4VcHaipKeyAttestationEvidenceIdentity $verifiedEvidenceIdentity = null
    ) {
        foreach ($verifiedKeyReferences as $reference) {
            if (trim($reference) === '') {
                throw new InvalidArgumentException(
                    'Verified key reference cannot be empty.'
                );
            }
        }

        foreach ($violations as $violation) {
            if (trim($violation) === '') {
                throw new InvalidArgumentException(
                    'Key attestation violation cannot be empty.'
                );
            }
        }

        if (!$valid && $verifiedKeyReferences !== []) {
            throw new InvalidArgumentException(
                'Invalid key attestation cannot expose verified keys.'
            );
        }

        if ($valid && $verifiedKeyReferences === []) {
            throw new InvalidArgumentException(
                'Valid key attestation requires verified keys.'
            );
        }

        if ($valid && $violations !== []) {
            throw new InvalidArgumentException(
                'Valid key attestation cannot contain violations.'
            );
        }

        $this->verifiedKeyReferences = $verifiedKeyReferences;
        $this->violations = $violations;
    }

    public function verifiedEvidenceIdentity(): ?OpenId4VcHaipKeyAttestationEvidenceIdentity
    {
        return $this->verifiedEvidenceIdentity;
    }
    /**
     * Identity authenticated by the Key Attestation verifier under its trusted
     * policy. It may belong to a Wallet Provider or a key-storage component,
     * and is not the Credential Issuer used to select that policy.
     * An unverified JWT iss claim MUST NOT be copied into this field.
     */    public function verifiedIssuerIdentifier(): ?string
    {
        return $this->verifiedIssuerIdentifier;
    }
    public function valid(): bool
    {
        return $this->valid;
    }

    /** @return list<string> */
    public function verifiedKeyReferences(): array
    {
        return $this->verifiedKeyReferences;
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }

    public function coversKey(string $keyReference): bool
    {
        return $this->valid
            && in_array(
                $keyReference,
                $this->verifiedKeyReferences,
                true
            );
    }

    /**
     * @param list<string> $requiredKeyReferences
     */
    public function coversAllKeys(array $requiredKeyReferences): bool
    {
        if (!$this->valid || $requiredKeyReferences === []) {
            return false;
        }

        foreach ($requiredKeyReferences as $reference) {
            if (!$this->coversKey($reference)) {
                return false;
            }
        }

        return true;
    }
}
