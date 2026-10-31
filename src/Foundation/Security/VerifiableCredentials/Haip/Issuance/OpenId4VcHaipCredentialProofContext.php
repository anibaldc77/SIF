<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;

final readonly class OpenId4VcHaipCredentialProofContext
{
    /** @var non-empty-list<string> */
    private array $requestedKeyReferences;

/**
 * @param list<string> $requestedKeyReferences
 */
    public function __construct(
        private string $issuerIdentifier,
        private string $credentialConfigurationId,
        array $requestedKeyReferences,
        private bool $proofVerified,
        private bool $nonceVerified,
        private bool $replaySafe,
        private bool $keyAttestationRequired,
        private ?OpenId4VcHaipKeyAttestation $keyAttestation = null,
        private ?OpenId4VcHaipKeyAttestationVerificationResult $keyAttestationVerification = null
    ) {
        if (trim($this->issuerIdentifier) === '') {
            throw new InvalidArgumentException('Issuer identifier is required.');
        }

        if (trim($this->credentialConfigurationId) === '') {
            throw new InvalidArgumentException(
                'Credential configuration identifier is required.'
            );
        }

        if ($requestedKeyReferences === []) {
            throw new InvalidArgumentException(
                'At least one requested key is required.'
            );
        }

        foreach ($requestedKeyReferences as $reference) {
            if (trim($reference) === '') {
                throw new InvalidArgumentException(
                    'Requested key reference is invalid.'
                );
            }
        }
        $this->requestedKeyReferences = $requestedKeyReferences;
    }

    public function issuerIdentifier(): string
    {
        return $this->issuerIdentifier;
    }

    public function credentialConfigurationId(): string
    {
        return $this->credentialConfigurationId;
    }

    /** @return non-empty-list<string> */
    public function requestedKeyReferences(): array
    {
        return $this->requestedKeyReferences;
    }

    public function proofVerified(): bool
    {
        return $this->proofVerified;
    }

    public function nonceVerified(): bool
    {
        return $this->nonceVerified;
    }

    public function replaySafe(): bool
    {
        return $this->replaySafe;
    }

    public function keyAttestationRequired(): bool
    {
        return $this->keyAttestationRequired;
    }

    public function keyAttestationVerification(): ?OpenId4VcHaipKeyAttestationVerificationResult
    {
        return $this->keyAttestationVerification;
    }

    public function keyAttestation(): ?OpenId4VcHaipKeyAttestation
    {
        return $this->keyAttestation;
    }
}
