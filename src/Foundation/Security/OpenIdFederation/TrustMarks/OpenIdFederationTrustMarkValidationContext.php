<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustMarks;

use DateTimeImmutable;

final readonly class OpenIdFederationTrustMarkValidationContext
{
    public function __construct(
        private DateTimeImmutable $evaluatedAt,
        private ?string $expectedSubjectEntityId = null,
        private ?string $expectedTrustMarkId = null
    ) {
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function expectedSubjectEntityId(): ?string
    {
        return $this->expectedSubjectEntityId;
    }

    public function expectedTrustMarkId(): ?string
    {
        return $this->expectedTrustMarkId;
    }
}
