<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwks;

use Sif\Foundation\Security\Contracts\JwkSetProviderInterface;

final class InMemoryJwkSetProvider implements JwkSetProviderInterface
{
    public function __construct(private JwkSet $set)
    {
    }

    public function get(): JwkSet
    {
        return $this->set;
    }

    public function refresh(): JwkSet
    {
        return $this->set;
    }

    public function replace(JwkSet $set): void
    {
        $this->set = $set;
    }
}
