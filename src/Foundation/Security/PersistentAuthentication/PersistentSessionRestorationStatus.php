<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

enum PersistentSessionRestorationStatus: string
{
    case Restored = 'restored';
    case Missing = 'missing';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case ReplaySuspected = 'replay_suspected';
    case IdentityUnavailable = 'identity_unavailable';
}
