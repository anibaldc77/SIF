<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class GovernanceException
{
    /**
     * @param list<CompensatingControl> $controls
     */
    public function __construct(
        private GovernanceExceptionId $id,
        private GovernanceSubjectId $subjectId,
        private ConflictRuleId $ruleId,
        private GovernanceExceptionStatus $status,
        private DateTimeImmutable $requestedAt,
        private DateTimeImmutable $expiresAt,
        private ?RiskAcceptance $riskAcceptance = null,
        private array $controls = []
    ) {
    }

    public function id(): GovernanceExceptionId
    {
        return $this->id;
    }

    public function subjectId(): GovernanceSubjectId
    {
        return $this->subjectId;
    }

    public function ruleId(): ConflictRuleId
    {
        return $this->ruleId;
    }

    public function status(): GovernanceExceptionStatus
    {
        return $this->status;
    }

    public function requestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function riskAcceptance(): ?RiskAcceptance
    {
        return $this->riskAcceptance;
    }

    /**
     * @return list<CompensatingControl>
     */
    public function controls(): array
    {
        return $this->controls;
    }

    public function activeAt(DateTimeImmutable $instant): bool
    {
        return $this->status->value()
            === GovernanceExceptionStatus::APPROVED
            && $instant >= $this->requestedAt
            && $instant < $this->expiresAt;
    }
}
