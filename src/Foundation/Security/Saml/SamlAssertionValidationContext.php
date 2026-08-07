<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateInterval;
use DateTimeImmutable;

final readonly class SamlAssertionValidationContext
{
    public function __construct(
        private SamlEntityId $expectedIssuer,
        private SamlEntityId $expectedAudience,
        private string $expectedRecipient,
        private ?SamlRequestId $expectedInResponseTo,
        private DateTimeImmutable $now,
        private DateInterval $clockSkew
    ) {
    }

    public function expectedIssuer(): SamlEntityId
    {
        return $this->expectedIssuer;
    }

    public function expectedAudience(): SamlEntityId
    {
        return $this->expectedAudience;
    }

    public function expectedRecipient(): string
    {
        return $this->expectedRecipient;
    }

    public function expectedInResponseTo(): ?SamlRequestId
    {
        return $this->expectedInResponseTo;
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function clockSkew(): DateInterval
    {
        return $this->clockSkew;
    }
}
