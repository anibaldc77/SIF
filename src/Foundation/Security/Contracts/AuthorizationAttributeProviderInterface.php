<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

interface AuthorizationAttributeProviderInterface
{
    public function provide(
        AuthenticatedPrincipal $principal
    ): AuthorizationAttributeBag;
}
