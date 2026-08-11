<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnDeviceMigrationContext
{
    /**
     * @param list<string> $sourceCredentialIds
     * @param list<string> $targetTransports
     */
    public function __construct(
        private string $userId,
        private array $sourceCredentialIds = [],
        private array $targetTransports = [],
        private bool $syncedCredentialAvailable = false
    ) {
    }

    public function userId(): string
    {
        return $this->userId;
    }

    /** @return list<string> */
    public function sourceCredentialIds(): array
    {
        return $this->sourceCredentialIds;
    }

    /** @return list<string> */
    public function targetTransports(): array
    {
        return $this->targetTransports;
    }

    public function syncedCredentialAvailable(): bool
    {
        return $this->syncedCredentialAvailable;
    }
}
