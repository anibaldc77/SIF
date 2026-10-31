<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet;

use DateTimeImmutable;

final readonly class OpenId4VcHaipWalletAuthenticationContext
{
    public function __construct(
        private OpenId4VcHaipWalletAttestation $attestation,
        private string $issuerEntityId,
        private ?string $clientId,
        private DateTimeImmutable $evaluatedAt,
        private bool $usedAtClientAuthenticationEndpoint
    ) {
    }

    public function attestation(): OpenId4VcHaipWalletAttestation
    {
        return $this->attestation;
    }

    public function issuerEntityId(): string
    {
        return $this->issuerEntityId;
    }

    public function clientId(): ?string
    {
        return $this->clientId;
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function usedAtClientAuthenticationEndpoint(): bool
    {
        return $this->usedAtClientAuthenticationEndpoint;
    }
}
