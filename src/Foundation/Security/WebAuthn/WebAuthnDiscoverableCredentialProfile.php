<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnDiscoverableCredentialProfile
{
    /**
     * @param list<string> $preferredTransports
     * @param list<string> $hints
     */
    public function __construct(
        private bool $discoverableRequired = true,
        private ?string $userVerification = 'preferred',
        private array $preferredTransports = [],
        private array $hints = []
    ) {
    }

    public function discoverableRequired(): bool
    {
        return $this->discoverableRequired;
    }

    public function userVerification(): ?string
    {
        return $this->userVerification;
    }

    /** @return list<string> */
    public function preferredTransports(): array
    {
        return $this->preferredTransports;
    }

    /** @return list<string> */
    public function hints(): array
    {
        return $this->hints;
    }
}
