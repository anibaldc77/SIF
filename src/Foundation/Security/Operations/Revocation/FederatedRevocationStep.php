<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

enum FederatedRevocationStep: string
{
    case LocalSessions = 'local_sessions';
    case ProviderCredentials = 'provider_credentials';
    case IdentityLink = 'identity_link';
}
