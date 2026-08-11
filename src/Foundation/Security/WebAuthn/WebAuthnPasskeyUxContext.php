<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnPasskeyUxContext
{
    /**
     * @param list<string> $availableCredentialIds
     * @param list<string> $availableTransports
     */
    public function __construct(
        private bool $conditionalMediationAvailable,
        private bool $usernamelessFlowAllowed,
        private array $availableCredentialIds = [],
        private array $availableTransports = []
    ) {
    }

    public function conditionalMediationAvailable(): bool
    {
        return $this->conditionalMediationAvailable;
    }

    public function usernamelessFlowAllowed(): bool
    {
        return $this->usernamelessFlowAllowed;
    }

    /** @return list<string> */
    public function availableCredentialIds(): array
    {
        return $this->availableCredentialIds;
    }

    /** @return list<string> */
    public function availableTransports(): array
    {
        return $this->availableTransports;
    }
}
