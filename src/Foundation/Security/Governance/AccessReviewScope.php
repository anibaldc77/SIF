<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class AccessReviewScope
{
    /**
     * @param list<GovernanceSubjectId> $subjects
     * @param list<EntitlementId> $entitlements
     */
    public function __construct(
        private array $subjects = [],
        private array $entitlements = []
    ) {
    }

    /**
     * @return list<GovernanceSubjectId>
     */
    public function subjects(): array
    {
        return $this->subjects;
    }

    /**
     * @return list<EntitlementId>
     */
    public function entitlements(): array
    {
        return $this->entitlements;
    }

    public function empty(): bool
    {
        return $this->subjects === []
            && $this->entitlements === [];
    }
}
