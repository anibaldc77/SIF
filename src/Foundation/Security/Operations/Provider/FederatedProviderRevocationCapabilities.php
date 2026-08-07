<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

final readonly class FederatedProviderRevocationCapabilities
{
    /** @param list<FederatedProviderRevocationCapability> $capabilities */
    public function __construct(private array $capabilities)
    {
    }

    public function supports(FederatedProviderRevocationCapability $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    /** @return list<FederatedProviderRevocationCapability> */
    public function all(): array
    {
        return $this->capabilities;
    }
}
