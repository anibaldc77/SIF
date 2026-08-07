<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

enum PersistentAuthenticationCredentialStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
}
