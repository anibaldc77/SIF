<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;

final readonly class SamlAssertionConditions
{
    /**
     * @param list<SamlEntityId> $audiences
     */
    public function __construct(
        private ?DateTimeImmutable $notBefore,
        private ?DateTimeImmutable $notOnOrAfter,
        private array $audiences
    ) {
    }

    public function notBefore(): ?DateTimeImmutable
    {
        return $this->notBefore;
    }

    public function notOnOrAfter(): ?DateTimeImmutable
    {
        return $this->notOnOrAfter;
    }

    /** @return list<SamlEntityId> */
    public function audiences(): array
    {
        return $this->audiences;
    }
}
