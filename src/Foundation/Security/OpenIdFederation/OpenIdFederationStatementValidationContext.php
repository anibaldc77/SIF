<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation;

use DateTimeImmutable;

final readonly class OpenIdFederationStatementValidationContext
{
    public function __construct(
        private DateTimeImmutable $evaluatedAt,
        private ?string $expectedEntityId = null
    ) {
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function expectedEntityId(): ?string
    {
        return $this->expectedEntityId;
    }
}
