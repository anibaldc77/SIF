<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\IdentityProvider;

enum IdentityAccountStatus: string
{
    case Active = 'active';
    case Disabled = 'disabled';
    case Locked = 'locked';
}
