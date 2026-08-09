<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class AccessReviewItem
{
    public function __construct(
        private AccessAssignment $assignment,
        private ?AccessReviewDecision $decision = null
    ) {
    }

    public function assignment(): AccessAssignment
    {
        return $this->assignment;
    }

    public function decision(): ?AccessReviewDecision
    {
        return $this->decision;
    }

    public function decided(): bool
    {
        return $this->decision !== null;
    }
}
