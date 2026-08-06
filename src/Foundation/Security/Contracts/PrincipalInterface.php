<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authentication\AuthenticationState;

interface PrincipalInterface
{
    public function authenticationState(): AuthenticationState;

    public function isAuthenticated(): bool;
}
