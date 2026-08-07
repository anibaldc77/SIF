<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

final readonly class FederatedProvisioningPolicy
{
    public function __construct(
        private bool $allowAutomaticProvisioning = false
    ) {
    }

    public function allowsAutomaticProvisioning(): bool
    {
        return $this->allowAutomaticProvisioning;
    }
}
