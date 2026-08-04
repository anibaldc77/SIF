<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

enum CookieSameSite: string
{
    case Lax = 'Lax';
    case Strict = 'Strict';
    case None = 'None';
}
