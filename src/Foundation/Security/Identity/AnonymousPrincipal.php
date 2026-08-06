<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Identity;

use Sif\Foundation\Security\Authentication\AuthenticationState;
use Sif\Foundation\Security\Contracts\PrincipalInterface;

final readonly class AnonymousPrincipal implements PrincipalInterface
{
    public function authenticationState(): AuthenticationState
    {
        return AuthenticationState::Anonymous;
    }

    public function isAuthenticated(): bool
    {
        return false;
    }
}
