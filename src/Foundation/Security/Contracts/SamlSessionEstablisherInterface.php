<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlAuthenticatedIdentity;

interface SamlSessionEstablisherInterface
{
    public function establish(
        SamlAuthenticatedIdentity $identity
    ): void;
}
