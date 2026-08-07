<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\Jwks\JwkSet;

interface JwkSetProviderInterface
{
    public function get(): JwkSet;

    public function refresh(): JwkSet;
}
