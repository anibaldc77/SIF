<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\EffectiveAccessAssignmentResolverInterface;

final readonly class AccessReviewWorkItemGenerator
{
    public function __construct(
        private EffectiveAccessAssignmentResolverInterface $assignments,
        private AccessReviewWorkItemFactory $factory
    ) {
    }

    /**
     * @return list<AccessReviewWorkItem>
     */
    public function generate(
        AccessReviewCampaign $campaign,
        GovernanceSubjectId $subjectId,
        DateTimeImmutable $at
    ): array {
        if (!$campaign->activeAt($at)) {
            return [];
        }

        $items = [];

        foreach (
            $this->assignments->resolve(
                $subjectId,
                $at
            ) as $assignment
        ) {
            if (!$this->inScope($campaign, $assignment)) {
                continue;
            }

            $items[] = $this->factory->create(
                $campaign,
                $assignment
            );
        }

        return $items;
    }

    private function inScope(
        AccessReviewCampaign $campaign,
        EffectiveAccessAssignment $assignment
    ): bool {
        $scope = $campaign->scope();

        if ($scope->empty()) {
            return true;
        }

        $subjectMatch = $scope->subjects() === [];

        foreach ($scope->subjects() as $subject) {
            if (
                $subject->value()
                === $assignment->assignment()->subjectId()->value()
            ) {
                $subjectMatch = true;
                break;
            }
        }

        $entitlementMatch = $scope->entitlements() === [];

        foreach ($scope->entitlements() as $entitlementId) {
            if (
                $entitlementId->value()
                === $assignment->entitlement()->id()->value()
            ) {
                $entitlementMatch = true;
                break;
            }
        }

        return $subjectMatch && $entitlementMatch;
    }
}
