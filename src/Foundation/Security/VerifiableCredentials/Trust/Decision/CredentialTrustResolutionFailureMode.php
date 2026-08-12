<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Decision;

enum CredentialTrustResolutionFailureMode: string
{
    case FailClosed = 'fail_closed';
    case AllowUsableStale = 'allow_usable_stale';
}
