<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

final readonly class CredentialIssuanceOperationalContext
{
    /**
     * @param list<string> $availableCapabilities
     * @param list<string> $activeControls
     */
    public function __construct(
        private array $availableCapabilities = [],
        private array $activeControls = []
    ) {
    }

    /**
     * @return list<string>
     */
    public function availableCapabilities(): array
    {
        return $this->availableCapabilities;
    }

    /**
     * @return list<string>
     */
    public function activeControls(): array
    {
        return $this->activeControls;
    }
}
