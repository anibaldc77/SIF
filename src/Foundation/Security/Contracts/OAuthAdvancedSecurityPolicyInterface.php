<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityCapability;

interface OAuthAdvancedSecurityPolicyInterface
{
    public function requires(
        OAuthAdvancedSecurityCapability $capability
    ): bool;
}
