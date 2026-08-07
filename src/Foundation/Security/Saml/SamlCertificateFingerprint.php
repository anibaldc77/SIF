<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlCertificateFingerprint
{
    public function __construct(private string $sha256)
    {
        if (preg_match('/^[a-f0-9]{64}$/', strtolower($this->sha256)) !== 1) {
            throw new InvalidArgumentException(
                'SAML certificate fingerprint must be a SHA-256 hex digest.'
            );
        }
    }

    public function sha256(): string
    {
        return strtolower($this->sha256);
    }
}
