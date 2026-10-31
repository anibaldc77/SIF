<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip;

enum OpenId4VcHaipFlow: string
{
    case Issuance = 'issuance';
    case PresentationRedirect = 'presentation_redirect';
    case PresentationDigitalCredentialsApi = 'presentation_digital_credentials_api';
}
