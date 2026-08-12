<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust;

enum CredentialTrustModel: string
{
    case Direct = 'direct';
    case Registry = 'registry';
    case Federation = 'federation';
    case Pki = 'pki';
}
