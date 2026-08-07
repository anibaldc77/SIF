<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authorization\Role\RoleSet;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

interface RoleResolverInterface
{
    public function resolve(AuthenticatedPrincipal $principal): RoleSet;
}
