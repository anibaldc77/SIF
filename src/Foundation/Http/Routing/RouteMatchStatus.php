<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing;

enum RouteMatchStatus: string
{
    case Matched = 'matched';
    case NotFound = 'not-found';
    case MethodNotAllowed = 'method-not-allowed';
}
