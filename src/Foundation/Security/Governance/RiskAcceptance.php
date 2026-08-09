<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class RiskAcceptance
{
    public function __construct(
        private GovernanceSubjectId $subjectId,
        private GovernanceRiskLevel $acceptedRisk,
        private string $reason,
        private DateTimeImmutable $acceptedAt,
        private DateTimeImmutable $expiresAt
    ) {
    }

    public function subjectId(): GovernanceSubjectId
    {
        return $this->subjectId;
    }

    public function acceptedRisk(): GovernanceRiskLevel
    {
        return $this->acceptedRisk;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function acceptedAt(): DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function validAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->acceptedAt
            && $instant < $this->expiresAt;
    }
}
