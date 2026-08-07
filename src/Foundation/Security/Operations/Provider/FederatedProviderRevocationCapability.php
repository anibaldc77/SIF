<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

enum FederatedProviderRevocationCapability: string
{
    case RevokeAccessToken = 'revoke_access_token';
    case RevokeRefreshToken = 'revoke_refresh_token';
    case EndSession = 'end_session';
    case GlobalLogout = 'global_logout';
}
