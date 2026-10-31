<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;

final readonly class OpenId4VcHaipKeyPossessionProofIdentity
{
    public function __construct(
        private string $sha256Fingerprint
    ) {
        if (
            preg_match(
                '/^[a-f0-9]{64}$/D',
                $this->sha256Fingerprint
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'HAIP key possession proof fingerprint must be a lowercase SHA-256 hex digest.'
            );
        }
    }

    public static function fromIssuanceProof(
        CredentialIssuanceProof $proof
    ): self {
        return new self(
            hash('sha256', $proof->serializedProof())
        );
    }
    public function algorithm(): string
    {
        return 'sha-256';
    }

    public function sha256Fingerprint(): string
    {
        return $this->sha256Fingerprint;
    }

    public function matches(self $other): bool
    {
        return hash_equals(
            $this->sha256Fingerprint,
            $other->sha256Fingerprint
        );
    }
}