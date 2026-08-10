<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiAuthorizationRequestSecurityRequirements;

interface FapiAuthorizationRequestSecurityRequirementsProviderInterface
{
    public function requirements(): FapiAuthorizationRequestSecurityRequirements;
}
