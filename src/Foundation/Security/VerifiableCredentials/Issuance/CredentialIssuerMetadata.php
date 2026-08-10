<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuerMetadata
{
    /**
     * @param array<string, CredentialConfiguration> $credentialConfigurations
     */
    public function __construct(
        private string $credentialIssuer,
        private string $credentialEndpoint,
        private array $credentialConfigurations,
        private ?string $batchCredentialEndpoint = null,
        private ?string $deferredCredentialEndpoint = null,
        private ?string $notificationEndpoint = null
    ) {
        if (
            trim($this->credentialIssuer) === ''
            || trim($this->credentialEndpoint) === ''
            || $this->credentialConfigurations === []
        ) {
            throw new InvalidArgumentException(
                'Credential issuer metadata is invalid.'
            );
        }
    }

    public function credentialIssuer(): string
    {
        return $this->credentialIssuer;
    }

    public function credentialEndpoint(): string
    {
        return $this->credentialEndpoint;
    }

    /**
     * @return array<string, CredentialConfiguration>
     */
    public function credentialConfigurations(): array
    {
        return $this->credentialConfigurations;
    }

    public function batchCredentialEndpoint(): ?string
    {
        return $this->batchCredentialEndpoint;
    }

    public function deferredCredentialEndpoint(): ?string
    {
        return $this->deferredCredentialEndpoint;
    }

    public function notificationEndpoint(): ?string
    {
        return $this->notificationEndpoint;
    }
}
