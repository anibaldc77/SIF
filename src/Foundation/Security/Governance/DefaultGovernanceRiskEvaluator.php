<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\EffectiveAccessAssignmentResolverInterface;
use Sif\Foundation\Security\Contracts\GovernanceRiskEvaluatorInterface;
use Sif\Foundation\Security\Contracts\SegregationOfDutiesRuleProviderInterface;

final readonly class DefaultGovernanceRiskEvaluator implements GovernanceRiskEvaluatorInterface
{
    public function __construct(
        private EffectiveAccessAssignmentResolverInterface $assignments,
        private SegregationOfDutiesRuleProviderInterface $rules
    ) {
    }

    public function evaluate(
        GovernanceSubjectId $subjectId,
        DateTimeImmutable $at
    ): GovernanceRiskAssessment {
        $assignments = $this->assignments->resolve(
            $subjectId,
            $at
        );

        $conflicts = [];

        foreach ($this->rules->all() as $rule) {
            $count = count($assignments);

            for ($left = 0; $left < $count; $left++) {
                for ($right = $left + 1; $right < $count; $right++) {
                    $leftAssignment = $assignments[$left];
                    $rightAssignment = $assignments[$right];

                    if (!$rule->conflicts(
                        $leftAssignment->entitlement()->id(),
                        $rightAssignment->entitlement()->id()
                    )) {
                        continue;
                    }

                    $conflicts[] = new GovernanceConflict(
                        $subjectId,
                        $rule,
                        $leftAssignment,
                        $rightAssignment
                    );
                }
            }
        }

        return new GovernanceRiskAssessment(
            $subjectId,
            $conflicts
        );
    }
}
