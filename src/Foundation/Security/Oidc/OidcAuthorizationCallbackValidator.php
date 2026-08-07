<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use Sif\Foundation\Security\Exceptions\InvalidOidcAuthorizationCallbackException;

final readonly class OidcAuthorizationCallbackValidator
{
    public function validate(
        OidcAuthorizationTransaction $transaction,
        OidcAuthorizationCallback $callback
    ): void {
        if (
            !hash_equals(
                $transaction->state()->value(),
                $callback->state()->value()
            )
        ) {
            throw new InvalidOidcAuthorizationCallbackException(
                'OIDC callback state does not match the authorization transaction.'
            );
        }
    }
}
