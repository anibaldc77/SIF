<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlIdentityProviderMetadata
{
    /**
     * @param list<SamlEndpoint> $singleSignOnServices
     * @param list<SamlEndpoint> $singleLogoutServices
     * @param list<SamlCertificateFingerprint> $signingCertificateFingerprints
     */
    public function __construct(
        private SamlEntityId $entityId,
        private array $singleSignOnServices,
        private array $singleLogoutServices = [],
        private array $signingCertificateFingerprints = []
    ) {
        if ($this->singleSignOnServices === []) {
            throw new InvalidArgumentException(
                'SAML identity provider metadata requires at least one SSO endpoint.'
            );
        }
    }

    public function entityId(): SamlEntityId
    {
        return $this->entityId;
    }

    /** @return list<SamlEndpoint> */
    public function singleSignOnServices(): array
    {
        return $this->singleSignOnServices;
    }

    /** @return list<SamlEndpoint> */
    public function singleLogoutServices(): array
    {
        return $this->singleLogoutServices;
    }

    /** @return list<SamlCertificateFingerprint> */
    public function signingCertificateFingerprints(): array
    {
        return $this->signingCertificateFingerprints;
    }
}
