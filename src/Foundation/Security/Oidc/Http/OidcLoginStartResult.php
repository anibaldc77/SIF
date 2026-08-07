<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Http;

use Sif\Foundation\Security\Oidc\OidcAuthorizationTransaction;

final readonly class OidcLoginStartResult
{
    public function __construct(
        private OidcAuthorizationTransaction $transaction,
        private OidcRedirectInstruction $redirect
    ) {
    }

    public function transaction(): OidcAuthorizationTransaction
    {
        return $this->transaction;
    }

    public function redirect(): OidcRedirectInstruction
    {
        return $this->redirect;
    }
}
