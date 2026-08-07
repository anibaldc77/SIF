<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;

final readonly class SamlAuthnRequest
{
    public function __construct(
        private SamlRequestId $id,
        private DateTimeImmutable $issueInstant,
        private SamlEntityId $issuer,
        private string $destination,
        private string $assertionConsumerServiceUrl,
        private bool $forceAuthn = false
    ) {
    }

    public function id(): SamlRequestId
    {
        return $this->id;
    }

    public function issueInstant(): DateTimeImmutable
    {
        return $this->issueInstant;
    }

    public function issuer(): SamlEntityId
    {
        return $this->issuer;
    }

    public function destination(): string
    {
        return $this->destination;
    }

    public function assertionConsumerServiceUrl(): string
    {
        return $this->assertionConsumerServiceUrl;
    }

    public function forceAuthn(): bool
    {
        return $this->forceAuthn;
    }
}
