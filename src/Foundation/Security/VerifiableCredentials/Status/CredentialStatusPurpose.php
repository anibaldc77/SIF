<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

enum CredentialStatusPurpose: string
{
    case Revocation = 'revocation';
    case Suspension = 'suspension';
}
