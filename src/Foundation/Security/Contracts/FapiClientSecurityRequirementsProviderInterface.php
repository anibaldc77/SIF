<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiClientSecurityRequirements;

interface FapiClientSecurityRequirementsProviderInterface
{
    public function requirements(): FapiClientSecurityRequirements;
}
