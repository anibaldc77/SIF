<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;

final readonly class SamlResponse
{
    public function __construct(
        private SamlResponseId $id,
        private DateTimeImmutable $issueInstant,
        private SamlEntityId $issuer,
        private SamlStatusCode $statusCode,
        private ?SamlRequestId $inResponseTo = null,
        private ?string $destination = null
    ) {
    }

    public function id(): SamlResponseId
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

    public function statusCode(): SamlStatusCode
    {
        return $this->statusCode;
    }

    public function inResponseTo(): ?SamlRequestId
    {
        return $this->inResponseTo;
    }

    public function destination(): ?string
    {
        return $this->destination;
    }
}
