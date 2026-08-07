<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

enum PkceMethod: string
{
    case S256 = 'S256';
}
