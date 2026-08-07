<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlCertificateFingerprint;
use Sif\Foundation\Security\Saml\SamlXmlSignatureVerificationResult;

interface SamlXmlSignatureVerifierInterface
{
    public function verify(
        string $xml,
        SamlCertificateFingerprint $expectedFingerprint
    ): SamlXmlSignatureVerificationResult;
}
