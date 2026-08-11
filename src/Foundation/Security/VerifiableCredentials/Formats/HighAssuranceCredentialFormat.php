<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

enum HighAssuranceCredentialFormat: string
{
    case SdJwtVc = 'dc+sd-jwt';
    case IsoMdoc = 'mso_mdoc';
}
