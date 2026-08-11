<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnRecoveryContext
{
    /**
     * @param list<string> $availableCredentialIds
     * @param list<string> $verifiedRecoveryFactors
     */
    public function __construct(
        private string $userId,
        private array $availableCredentialIds = [],
        private array $verifiedRecoveryFactors = [],
        private bool $highRisk = false
    ) {
    }

    public function userId(): string
    {
        return $this->userId;
    }

    /**
     * @return list<string>
     */
    public function availableCredentialIds(): array
    {
        return $this->availableCredentialIds;
    }

    /**
     * @return list<string>
     */
    public function verifiedRecoveryFactors(): array
    {
        return $this->verifiedRecoveryFactors;
    }

    public function highRisk(): bool
    {
        return $this->highRisk;
    }
}
