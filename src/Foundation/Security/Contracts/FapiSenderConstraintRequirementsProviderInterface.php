<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiSenderConstraintRequirements;

interface FapiSenderConstraintRequirementsProviderInterface
{
    public function requirements(): FapiSenderConstraintRequirements;
}
