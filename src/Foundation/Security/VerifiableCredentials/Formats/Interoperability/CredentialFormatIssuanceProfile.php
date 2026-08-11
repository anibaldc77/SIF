<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProfile;

final readonly class CredentialFormatIssuanceProfile
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $credentialConfigurationId,
        private CredentialFormatProfile $formatProfile,
        private array $metadata = []
    ) {
        if (trim($this->credentialConfigurationId) === '') {
            throw new InvalidArgumentException(
                'Credential format issuance profile is invalid.'
            );
        }
    }

    public function credentialConfigurationId(): string
    {
        return $this->credentialConfigurationId;
    }

    public function formatProfile(): CredentialFormatProfile
    {
        return $this->formatProfile;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
