<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlResponseValidationContext
{
    public function __construct(
        private SamlEntityId $expectedIssuer,
        private string $expectedDestination,
        private ?SamlRequestId $expectedInResponseTo
    ) {
    }

    public function expectedIssuer(): SamlEntityId
    {
        return $this->expectedIssuer;
    }

    public function expectedDestination(): string
    {
        return $this->expectedDestination;
    }

    public function expectedInResponseTo(): ?SamlRequestId
    {
        return $this->expectedInResponseTo;
    }
}
