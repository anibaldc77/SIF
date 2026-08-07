<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Authorization;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class OAuthAccessTokenAuthorizationAttributes
{
    public function from(
        ValidatedAccessToken $token
    ): AuthorizationAttributeBag {
        $attributes = [
            'oauth.subject' => $token->subject()->value(),
            'oauth.scope.count' => $token->scopes()->count(),
        ];

        foreach ($token->attributes() as $name => $value) {
            $attributes['oauth.claim.' . $name] = $value;
        }

        return new AuthorizationAttributeBag($attributes);
    }
}
