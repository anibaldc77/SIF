<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiDeploymentContext
{
    /**
     * @param list<string> $availableCapabilities
     * @param list<string> $activeSecurityControls
     */
    public function __construct(
        private array $availableCapabilities = [],
        private array $activeSecurityControls = []
    ) {
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
}
