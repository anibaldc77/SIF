<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlSignatureValidationContext
{
    public function __construct(
        private SamlEntityId $issuer,
        private SamlCertificateFingerprint $fingerprint,
        private string $responseXml,
        private ?string $assertionXml = null
    ) {
    }

    public function issuer(): SamlEntityId
    {
        return $this->issuer;
    }

    public function fingerprint(): SamlCertificateFingerprint
    {
        return $this->fingerprint;
    }

    public function responseXml(): string
    {
        return $this->responseXml;
    }

    public function assertionXml(): ?string
    {
        return $this->assertionXml;
    }
}
