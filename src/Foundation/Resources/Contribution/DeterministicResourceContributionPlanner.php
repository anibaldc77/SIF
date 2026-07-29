<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contribution;

use Sif\Foundation\Resources\Contracts\ResourceContributionPlannerInterface;
use Sif\Foundation\Resources\Exceptions\ResourceOverrideConflictException;

final readonly class DeterministicResourceContributionPlanner implements ResourceContributionPlannerInterface
{
    public function compile(array $contributions): CompiledResourceContributionPlan
    {
        /** @var array<string, PlannedResourceContribution> $effective */
        $effective = [];
        /** @var list<ResourceOverrideDecision> $decisions */
        $decisions = [];

        foreach ($contributions as $order => $contribution) {
            $candidate = new PlannedResourceContribution($contribution, $order);
            $key = $contribution->descriptor()->qualifiedIdentifier();
            $current = $effective[$key] ?? null;

            if ($current === null) {
                $effective[$key] = $candidate;
                continue;
            }

            $policy = $contribution->overridePolicy();
            if (!$policy->permitsReplacement()) {
                throw ResourceOverrideConflictException::forbidden($key, $contribution->moduleId()->value());
            }

            if ($policy->requiresHigherPriority()) {
                $candidatePriority = $contribution->descriptor()->priority();
                $currentPriority = $current->contribution()->descriptor()->priority();
                if ($candidatePriority->compare($currentPriority) <= 0) {
                    throw ResourceOverrideConflictException::insufficientPriority(
                        $key,
                        $contribution->moduleId()->value(),
                        $candidatePriority->value(),
                        $currentPriority->value(),
                    );
                }
            }

            $effective[$key] = $candidate;
            $decisions[] = new ResourceOverrideDecision($candidate, $current);
        }

        $ordered = array_values($effective);
        usort($ordered, static function (PlannedResourceContribution $left, PlannedResourceContribution $right): int {
            $priority = $right->contribution()->descriptor()->priority()
                ->compare($left->contribution()->descriptor()->priority());
            if ($priority !== 0) {
                return $priority;
            }

            return $left->contributionOrder() <=> $right->contributionOrder();
        });

        return new CompiledResourceContributionPlan($ordered, $decisions);
    }
}
