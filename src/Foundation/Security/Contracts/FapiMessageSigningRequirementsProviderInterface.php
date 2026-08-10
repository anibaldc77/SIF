<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiMessageSigningRequirements;

interface FapiMessageSigningRequirementsProviderInterface
{
    public function requirements(): FapiMessageSigningRequirements;
}
