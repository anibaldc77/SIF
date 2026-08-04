<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

enum CsrfFailureReason: string
{
    case MissingSession = 'missing-session';
    case MissingExpectedToken = 'missing-expected-token';
    case MissingSubmittedToken = 'missing-submitted-token';
    case InvalidSubmittedToken = 'invalid-submitted-token';
}
