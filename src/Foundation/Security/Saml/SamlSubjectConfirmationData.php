<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;

final readonly class SamlSubjectConfirmationData
{
    public function __construct(
        private ?string $recipient,
        private ?SamlRequestId $inResponseTo,
        private ?DateTimeImmutable $notOnOrAfter
    ) {
    }

    public function recipient(): ?string
    {
        return $this->recipient;
    }

    public function inResponseTo(): ?SamlRequestId
    {
        return $this->inResponseTo;
    }

    public function notOnOrAfter(): ?DateTimeImmutable
    {
        return $this->notOnOrAfter;
    }
}
