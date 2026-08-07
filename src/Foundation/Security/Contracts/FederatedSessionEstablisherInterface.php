<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

interface FederatedSessionEstablisherInterface
{
    public function establish(
        AuthenticatedPrincipal $principal
    ): void;
}
