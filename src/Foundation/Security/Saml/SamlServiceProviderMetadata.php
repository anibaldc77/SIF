<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlServiceProviderMetadata
{
    public function __construct(
        private SamlEntityId $entityId,
        private SamlEndpoint $assertionConsumerService,
        private ?SamlEndpoint $singleLogoutService = null
    ) {
    }

    public function entityId(): SamlEntityId
    {
        return $this->entityId;
    }

    public function assertionConsumerService(): SamlEndpoint
    {
        return $this->assertionConsumerService;
    }

    public function singleLogoutService(): ?SamlEndpoint
    {
        return $this->singleLogoutService;
    }
}
