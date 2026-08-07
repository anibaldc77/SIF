<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use Sif\Foundation\Security\Contracts\SamlTrustStoreInterface;
use Sif\Foundation\Security\Contracts\SamlXmlSignatureVerifierInterface;

final readonly class SamlSignatureTrustValidator
{
    public function __construct(
        private SamlTrustStoreInterface $trustStore,
        private SamlXmlSignatureVerifierInterface $signatureVerifier
    ) {
    }

    public function validate(
        SamlEntityId $issuer,
        string $signedXml,
        SamlCertificateFingerprint $fingerprint
    ): SamlXmlSignatureVerificationResult {
        if (!$this->trustStore->trusts($issuer, $fingerprint)) {
            return SamlXmlSignatureVerificationResult::failed([
                'certificate_not_trusted',
            ]);
        }

        return $this->signatureVerifier->verify(
            $signedXml,
            $fingerprint
        );
    }
}
