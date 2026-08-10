<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiDeploymentProfile;

interface FapiDeploymentProfileProviderInterface
{
    public function profile(): FapiDeploymentProfile;
}
