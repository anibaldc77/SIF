<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;

final readonly class SamlAssertion
{
    public function __construct(
        private SamlAssertionId $id,
        private DateTimeImmutable $issueInstant,
        private SamlEntityId $issuer,
        private SamlNameId $subject,
        private SamlAssertionConditions $conditions,
        private ?SamlSubjectConfirmationData $subjectConfirmationData = null
    ) {
    }

    public function id(): SamlAssertionId
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

    public function subject(): SamlNameId
    {
        return $this->subject;
    }

    public function conditions(): SamlAssertionConditions
    {
        return $this->conditions;
    }

    public function subjectConfirmationData(): ?SamlSubjectConfirmationData
    {
        return $this->subjectConfirmationData;
    }
}
