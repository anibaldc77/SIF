<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

enum AuthenticationState: string
{
    case Anonymous = 'anonymous';
    case Authenticated = 'authenticated';
}
