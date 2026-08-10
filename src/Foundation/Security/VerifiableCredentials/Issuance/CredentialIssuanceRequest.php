<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceRequest
{
    /**
     * @param array<string, mixed> $proof
     */
    public function __construct(
        private string $credentialConfigurationId,
        private array $proof = [],
        private ?string $credentialIdentifier = null
    ) {
        if (trim($this->credentialConfigurationId) === '') {
            throw new InvalidArgumentException(
                'Credential issuance request is invalid.'
            );
        }
    }

    public function credentialConfigurationId(): string
    {
        return $this->credentialConfigurationId;
    }

    /**
     * @return array<string, mixed>
     */
    public function proof(): array
    {
        return $this->proof;
    }

    public function credentialIdentifier(): ?string
    {
        return $this->credentialIdentifier;
    }
}
