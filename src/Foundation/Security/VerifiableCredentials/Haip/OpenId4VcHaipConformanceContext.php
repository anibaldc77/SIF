<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip;

final readonly class OpenId4VcHaipConformanceContext
{
    /**
     * @param list<string> $availableCapabilities
     * @param list<string> $activeSecurityControls
     */
    public function __construct(
        private OpenId4VcHaipProfile $profile,
        private array $availableCapabilities = [],
        private array $activeSecurityControls = []
    ) {
    }

    public function profile(): OpenId4VcHaipProfile
    {
        return $this->profile;
    }

    /** @return list<string> */
    public function availableCapabilities(): array
    {
        return $this->availableCapabilities;
    }

    /** @return list<string> */
    public function activeSecurityControls(): array
    {
        return $this->activeSecurityControls;
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->availableCapabilities, true);
    }

    public function hasSecurityControl(string $control): bool
    {
        return in_array($control, $this->activeSecurityControls, true);
    }
}
