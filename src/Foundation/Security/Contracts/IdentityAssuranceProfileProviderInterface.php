<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceProfile;

interface IdentityAssuranceProfileProviderInterface
{
    public function profile(): IdentityAssuranceProfile;
}
