<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiMetadataConformanceRequirements;

interface FapiMetadataConformanceRequirementsProviderInterface
{
    public function requirements(): FapiMetadataConformanceRequirements;
}
