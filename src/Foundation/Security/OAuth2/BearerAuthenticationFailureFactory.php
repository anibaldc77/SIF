<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

final readonly class BearerAuthenticationFailureFactory
{
    public function invalidRequest(
        string $realm,
        ?string $description = null
    ): BearerAuthenticationFailure {
        $error = new BearerError(
            BearerErrorCode::InvalidRequest,
            $description
        );

        return new BearerAuthenticationFailure(
            400,
            $error,
            new BearerChallenge($realm, $error)
        );
    }

    public function invalidToken(
        string $realm,
        ?string $description = null
    ): BearerAuthenticationFailure {
        $error = new BearerError(
            BearerErrorCode::InvalidToken,
            $description
        );

        return new BearerAuthenticationFailure(
            401,
            $error,
            new BearerChallenge($realm, $error)
        );
    }

    public function insufficientScope(
        string $realm,
        ?string $requiredScope = null,
        ?string $description = null
    ): BearerAuthenticationFailure {
        $error = new BearerError(
            BearerErrorCode::InsufficientScope,
            $description,
            $requiredScope
        );

        return new BearerAuthenticationFailure(
            403,
            $error,
            new BearerChallenge($realm, $error)
        );
    }
}
