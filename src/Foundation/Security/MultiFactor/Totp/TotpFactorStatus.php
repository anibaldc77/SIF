<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

enum TotpFactorStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Revoked = 'revoked';
}
