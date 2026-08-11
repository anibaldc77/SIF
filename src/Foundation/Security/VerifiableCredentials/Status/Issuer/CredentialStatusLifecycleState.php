<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Issuer;

enum CredentialStatusLifecycleState: string
{
    case Valid = 'valid';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
}
