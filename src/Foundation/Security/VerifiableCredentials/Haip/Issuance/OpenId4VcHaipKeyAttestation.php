<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;

final readonly class OpenId4VcHaipKeyAttestation
{
    /** @var non-empty-list<string> */
    private array $attestedKeyReferences;

    /**
     * @param list<string> $attestedKeyReferences
     */
    public function __construct(
        private string $attestationFormat,
        array $attestedKeyReferences,
        private ?OpenId4VcHaipKeyAttestationEvidenceIdentity $evidenceIdentity = null,
        private ?string $serializedJwt = null
    ) {
        if (trim($this->attestationFormat) === '') {
            throw new InvalidArgumentException(
                'HAIP key attestation format is invalid.'
            );
        }

        if ($attestedKeyReferences === []) {
            throw new InvalidArgumentException(
                'HAIP key attestation requires attested keys.'
            );
        }

        foreach ($attestedKeyReferences as $reference) {
            if (trim($reference) === '') {
                throw new InvalidArgumentException(
                    'HAIP attested key reference is invalid.'
                );
            }
        }
        $this->attestedKeyReferences = $attestedKeyReferences;
    }

    public function evidenceIdentity(): ?OpenId4VcHaipKeyAttestationEvidenceIdentity
    {
        return $this->evidenceIdentity;
    }
    /**
     * Untrusted compact JWT supplied by the wallet, for cryptographic verification.
     * This value and evidenceIdentity() do not establish authenticity or trust.
     */
    public function serializedJwt(): ?string
    {
        return $this->serializedJwt;
    }
    public function attestationFormat(): string
    {
        return $this->attestationFormat;
    }

    /** @return non-empty-list<string> */
    public function attestedKeyReferences(): array
    {
        return $this->attestedKeyReferences;
    }
}
