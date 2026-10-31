<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;

final readonly class OpenId4VcHaipKeyPossessionVerificationResult
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
        private ?OpenId4VcHaipKeyPossessionProofIdentity $verifiedProofIdentity = null,
        private ?string $verifiedProofType = null
    ) {
        foreach ($verifiedKeyReferences as $reference) {
            if (trim($reference) === '') {
                throw new InvalidArgumentException(
                    'Verified possession key reference cannot be empty.'
                );
            }
        }

        foreach ($violations as $violation) {
            if (trim($violation) === '') {
                throw new InvalidArgumentException(
                    'Key possession violation cannot be empty.'
                );
            }
        }

        if (!$valid && $verifiedKeyReferences !== []) {
            throw new InvalidArgumentException(
                'Invalid key possession cannot expose verified keys.'
            );
        }

        if ($valid && $verifiedKeyReferences === []) {
            throw new InvalidArgumentException(
                'Valid key possession requires verified keys.'
            );
        }

        if ($valid && $violations !== []) {
            throw new InvalidArgumentException(
                'Valid key possession cannot contain violations.'
            );
        }

        $this->verifiedKeyReferences = $verifiedKeyReferences;
        $this->violations = $violations;
    }

    public function verifiedIssuerIdentifier(): ?string
    {
        return $this->verifiedIssuerIdentifier;
    }
    public function verifiedProofIdentity(): ?OpenId4VcHaipKeyPossessionProofIdentity
    {
        return $this->verifiedProofIdentity;
    }
    public function verifiedProofType(): ?string
    {
        return $this->verifiedProofType;
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