<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\Http\OidcRedirectInstruction;
use Sif\Foundation\Security\Oidc\Logout\OidcLogoutRequest;

interface OidcFederatedLogoutProviderInterface
{
    public function createRedirect(
        OidcLogoutRequest $request
    ): OidcRedirectInstruction;
}
