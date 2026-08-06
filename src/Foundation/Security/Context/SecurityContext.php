<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Context;

use Sif\Foundation\Security\Contracts\PrincipalInterface;
use Sif\Foundation\Security\Identity\AnonymousPrincipal;

final class SecurityContext
{
    private PrincipalInterface $principal;

    public function __construct(?PrincipalInterface $principal = null)
    {
        $this->principal = $principal ?? new AnonymousPrincipal();
    }

    public function principal(): PrincipalInterface
    {
        return $this->principal;
    }

    public function isAuthenticated(): bool
    {
        return $this->principal->isAuthenticated();
    }

    public function replace(PrincipalInterface $principal): void
    {
        $this->principal = $principal;
    }

    public function clear(): void
    {
        $this->principal = new AnonymousPrincipal();
    }
}
