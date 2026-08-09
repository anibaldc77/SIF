<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use Sif\Foundation\Security\Exceptions\InvalidOAuthRedirectUriException;

final readonly class OAuthRedirectUriValidator
{
    public function assertAllowed(
        OAuthClient $client,
        OAuthRedirectUri $redirectUri
    ): void {
        foreach ($client->redirectUris() as $allowed) {
            if ($allowed->value() === $redirectUri->value()) {
                return;
            }
        }

        throw new InvalidOAuthRedirectUriException(
            'OAuth redirect URI is not registered for the client.'
        );
    }
}
