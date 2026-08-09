<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityProfile;

interface OAuthAdvancedSecurityProfileProviderInterface
{
    public function current(): OAuthAdvancedSecurityProfile;
}
