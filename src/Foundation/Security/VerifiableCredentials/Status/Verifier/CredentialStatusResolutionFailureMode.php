<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Verifier;

enum CredentialStatusResolutionFailureMode: string
{
    case FailClosed = 'fail_closed';
    case AllowUsableStale = 'allow_usable_stale';
}
