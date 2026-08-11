<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

use InvalidArgumentException;

final readonly class SdJwtVcCredentialPayload
{
    /**
     * @param array<string, mixed> $claims
     * @param list<SdJwtVcDisclosureReference> $disclosureReferences
     */
    public function __construct(
        private string $issuer,
        private string $vct,
        private array $claims,
        private array $disclosureReferences = [],
        private ?string $subject = null
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->vct) === ''
        ) {
            throw new InvalidArgumentException(
                'SD-JWT VC credential payload is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function vct(): string
    {
        return $this->vct;
    }

    /**
     * @return array<string, mixed>
     */
    public function claims(): array
    {
        return $this->claims;
    }

    /**
     * @return list<SdJwtVcDisclosureReference>
     */
    public function disclosureReferences(): array
    {
        return $this->disclosureReferences;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }
}
