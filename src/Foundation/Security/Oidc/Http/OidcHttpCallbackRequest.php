<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Http;

use Sif\Foundation\Security\Oidc\OidcAuthorizationCallback;

final readonly class OidcHttpCallbackRequest
{
    public function __construct(
        private OidcAuthorizationCallback $callback
    ) {
    }

    public function callback(): OidcAuthorizationCallback
    {
        return $this->callback;
    }
}
