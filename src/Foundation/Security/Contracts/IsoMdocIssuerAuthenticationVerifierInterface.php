<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocIssuerAuthenticationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocIssuerSignedData;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocMobileSecurityObject;

interface IsoMdocIssuerAuthenticationVerifierInterface
{
    public function verify(
        IsoMdocIssuerSignedData $issuerSigned,
        IsoMdocMobileSecurityObject $mso
    ): IsoMdocIssuerAuthenticationAssessment;
}
