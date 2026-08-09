<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class GovernanceConflict
{
    public function __construct(
        private GovernanceSubjectId $subjectId,
        private SegregationOfDutiesRule $rule,
        private EffectiveAccessAssignment $leftAssignment,
        private EffectiveAccessAssignment $rightAssignment
    ) {
    }

    public function subjectId(): GovernanceSubjectId
    {
        return $this->subjectId;
    }

    public function rule(): SegregationOfDutiesRule
    {
        return $this->rule;
    }

    public function leftAssignment(): EffectiveAccessAssignment
    {
        return $this->leftAssignment;
    }

    public function rightAssignment(): EffectiveAccessAssignment
    {
        return $this->rightAssignment;
    }
}
