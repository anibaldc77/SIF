<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenStatus;

interface OAuthTokenStatusProviderInterface
{
    public function status(string $token): ?OAuthTokenStatus;
}
