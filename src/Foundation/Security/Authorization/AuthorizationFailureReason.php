<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

enum AuthorizationFailureReason: string
{
    case NONE = 'none';
    case ANONYMOUS = 'anonymous';
    case NOT_AUTHORIZED = 'not_authorized';
    case NO_APPLICABLE_POLICY = 'no_applicable_policy';
    case TECHNICAL_FAILURE = 'technical_failure';
}
