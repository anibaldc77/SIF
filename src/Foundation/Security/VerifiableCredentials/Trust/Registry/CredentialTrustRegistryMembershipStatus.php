<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Registry;

enum CredentialTrustRegistryMembershipStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
