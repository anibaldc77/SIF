<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlRelayState;

interface SamlRelayStateGeneratorInterface
{
    public function generate(): SamlRelayState;
}
