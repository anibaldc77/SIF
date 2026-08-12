<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust;

enum CredentialTrustEntityRole: string
{
    case Issuer = 'issuer';
    case Verifier = 'verifier';
    case Wallet = 'wallet';
    case TrustAnchor = 'trust_anchor';
    case AccreditationAuthority = 'accreditation_authority';
}
