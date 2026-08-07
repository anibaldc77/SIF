<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequest;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeResult;

interface OidcTokenExchangerInterface
{
    public function exchange(
        OidcTokenExchangeRequest $request
    ): OidcTokenExchangeResult;
}
