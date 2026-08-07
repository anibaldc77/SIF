<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

final readonly class OidcAuthorizationCallback
{
    public function __construct(
        private OidcState $state,
        private OidcAuthorizationCode $code
    ) {
    }

    public function state(): OidcState
    {
        return $this->state;
    }

    public function code(): OidcAuthorizationCode
    {
        return $this->code;
    }
}
