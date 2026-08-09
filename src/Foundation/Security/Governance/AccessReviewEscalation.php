<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class AccessReviewEscalation
{
    public function __construct(
        private AccessReviewerId $fromReviewer,
        private AccessReviewerId $toReviewer,
        private DateTimeImmutable $escalatedAt,
        private string $reason
    ) {
    }

    public function fromReviewer(): AccessReviewerId
    {
        return $this->fromReviewer;
    }

    public function toReviewer(): AccessReviewerId
    {
        return $this->toReviewer;
    }

    public function escalatedAt(): DateTimeImmutable
    {
        return $this->escalatedAt;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
