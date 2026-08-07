<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

enum FederatedRevocationScope: string
{
    case LocalSessions = 'local_sessions';
    case ProviderCredentials = 'provider_credentials';
    case IdentityLink = 'identity_link';
    case All = 'all';
}
