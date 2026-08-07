<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Logout;

use Sif\Foundation\Security\Contracts\OidcFederatedLogoutProviderInterface;
use Sif\Foundation\Security\Oidc\Http\OidcRedirectInstruction;

final readonly class StandardOidcLogoutRedirectProvider implements OidcFederatedLogoutProviderInterface
{
    public function createRedirect(
        OidcLogoutRequest $request
    ): OidcRedirectInstruction {
        $query = [];

        $idTokenHint = $request->idTokenHint();
        if ($idTokenHint !== null) {
            $query['id_token_hint'] = $idTokenHint->expose(
                static fn (string $value): string => $value
            );
        }

        $postLogoutRedirectUri = $request->postLogoutRedirectUri();
        if ($postLogoutRedirectUri !== null) {
            $query['post_logout_redirect_uri'] = $postLogoutRedirectUri;
        }

        return new OidcRedirectInstruction(
            $request->endSessionEndpoint(),
            $query
        );
    }
}
