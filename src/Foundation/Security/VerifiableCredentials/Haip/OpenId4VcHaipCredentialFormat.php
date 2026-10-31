<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip;

enum OpenId4VcHaipCredentialFormat: string
{
    case SdJwtVc = 'dc+sd-jwt';
    case IsoMdoc = 'mso_mdoc';
}
