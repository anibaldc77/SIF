<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcState;

interface OidcStateGeneratorInterface
{
    public function generate(): OidcState;
}
