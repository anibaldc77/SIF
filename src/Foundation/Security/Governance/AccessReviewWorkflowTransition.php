<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class AccessReviewWorkflowTransition
{
    public function __construct(
        private AccessReviewWorkflowStatus $from,
        private AccessReviewWorkflowStatus $to,
        private AccessReviewerId $actor,
        private DateTimeImmutable $occurredAt
    ) {
    }

    public function from(): AccessReviewWorkflowStatus
    {
        return $this->from;
    }

    public function to(): AccessReviewWorkflowStatus
    {
        return $this->to;
    }

    public function actor(): AccessReviewerId
    {
        return $this->actor;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
