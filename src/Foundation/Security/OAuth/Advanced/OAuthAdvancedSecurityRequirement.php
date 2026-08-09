<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

final readonly class OAuthAdvancedSecurityRequirement
{
    public function __construct(
        private OAuthAdvancedSecurityCapability $capability,
        private bool $required
    ) {
    }

    public function capability(): OAuthAdvancedSecurityCapability
    {
        return $this->capability;
    }

    public function required(): bool
    {
        return $this->required;
    }
}
