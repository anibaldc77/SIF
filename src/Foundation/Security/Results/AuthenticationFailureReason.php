<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Results;

enum AuthenticationFailureReason: string
{
    case InvalidCredentials = 'invalid_credentials';
    case UnsupportedCredentials = 'unsupported_credentials';
    case IdentityUnavailable = 'identity_unavailable';
    case InsufficientEvidence = 'insufficient_evidence';
    case Rejected = 'rejected';
    case InfrastructureFailure = 'infrastructure_failure';
}
