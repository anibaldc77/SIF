<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class GovernanceExceptionDecision
{
    public function __construct(
        private GovernanceExceptionStatus $status,
        private AccessReviewerId $approverId,
        private DateTimeImmutable $decidedAt,
        private ?string $reason = null
    ) {
    }

    public function status(): GovernanceExceptionStatus
    {
        return $this->status;
    }

    public function approverId(): AccessReviewerId
    {
        return $this->approverId;
    }

    public function decidedAt(): DateTimeImmutable
    {
        return $this->decidedAt;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
