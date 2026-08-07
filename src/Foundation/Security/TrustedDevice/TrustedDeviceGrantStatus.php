<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

enum TrustedDeviceGrantStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
}
