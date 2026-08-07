<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

enum BearerErrorCode: string
{
    case InvalidRequest = 'invalid_request';
    case InvalidToken = 'invalid_token';
    case InsufficientScope = 'insufficient_scope';
}
