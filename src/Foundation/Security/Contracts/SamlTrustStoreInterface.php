<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlCertificateFingerprint;
use Sif\Foundation\Security\Saml\SamlEntityId;

interface SamlTrustStoreInterface
{
    public function trusts(
        SamlEntityId $entityId,
        SamlCertificateFingerprint $fingerprint
    ): bool;
}
