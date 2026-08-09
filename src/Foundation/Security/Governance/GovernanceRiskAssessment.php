<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class GovernanceRiskAssessment
{
    /**
     * @param list<GovernanceConflict> $conflicts
     */
    public function __construct(
        private GovernanceSubjectId $subjectId,
        private array $conflicts
    ) {
    }

    public function subjectId(): GovernanceSubjectId
    {
        return $this->subjectId;
    }

    /**
     * @return list<GovernanceConflict>
     */
    public function conflicts(): array
    {
        return $this->conflicts;
    }

    public function score(): int
    {
        $score = 0;

        foreach ($this->conflicts as $conflict) {
            $score += $conflict->rule()->risk()->weight();
        }

        return min(100, $score);
    }

    public function hasConflicts(): bool
    {
        return $this->conflicts !== [];
    }
}
