<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenRevocationRequest;

interface OAuthTokenRevokerInterface
{
    public function revoke(
        OAuthTokenRevocationRequest $request
    ): void;
}
