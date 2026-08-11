<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceAuthenticationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceAuthenticationContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceSignedData;

interface IsoMdocDeviceAuthenticationVerifierInterface
{
    public function verify(
        IsoMdocDeviceSignedData $deviceSigned,
        IsoMdocDeviceAuthenticationContext $context
    ): IsoMdocDeviceAuthenticationAssessment;
}
