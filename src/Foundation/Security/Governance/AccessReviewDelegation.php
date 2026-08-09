<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class AccessReviewDelegation
{
    public function __construct(
        private AccessReviewerId $fromReviewer,
        private AccessReviewerId $toReviewer,
        private DateTimeImmutable $delegatedAt,
        private ?string $reason = null
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

    public function delegatedAt(): DateTimeImmutable
    {
        return $this->delegatedAt;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
