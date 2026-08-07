<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

interface PermissionResolverInterface
{
    public function resolve(AuthenticatedPrincipal $principal): PermissionSet;
}
