<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipAuthorizationRequirements;

interface OpenId4VcHaipAuthorizationRequirementsProviderInterface
{
    public function requirements(): OpenId4VcHaipAuthorizationRequirements;
}
