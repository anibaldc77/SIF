<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlX509Certificate
{
    public function __construct(private string $base64Der)
    {
        $normalized = preg_replace('/\s+/', '', $this->base64Der);

        if (
            !is_string($normalized)
            || $normalized === ''
            || base64_decode($normalized, true) === false
        ) {
            throw new InvalidArgumentException(
                'SAML X.509 certificate material is invalid.'
            );
        }
    }

    public function base64Der(): string
    {
        $normalized = preg_replace('/\s+/', '', $this->base64Der);

        return is_string($normalized) ? $normalized : '';
    }

    public function fingerprint(): SamlCertificateFingerprint
    {
        $decoded = base64_decode($this->base64Der(), true);

        if ($decoded === false) {
            throw new InvalidArgumentException(
                'SAML X.509 certificate material is invalid.'
            );
        }

        return new SamlCertificateFingerprint(
            hash('sha256', $decoded)
        );
    }
}
