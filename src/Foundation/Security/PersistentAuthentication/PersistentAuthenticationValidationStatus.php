<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

enum PersistentAuthenticationValidationStatus: string
{
    case Accepted = 'accepted';
    case Missing = 'missing';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case ReplaySuspected = 'replay_suspected';
}
