<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcNonce;

interface OidcNonceGeneratorInterface
{
    public function generate(): OidcNonce;
}
