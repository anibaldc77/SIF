<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class AccessReviewDeadline
{
    public function __construct(
        private DateTimeImmutable $dueAt
    ) {
    }

    public function dueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function overdueAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->dueAt;
    }
}
