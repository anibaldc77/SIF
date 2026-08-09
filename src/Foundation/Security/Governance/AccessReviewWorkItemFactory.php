<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use Sif\Foundation\Security\Contracts\AccessReviewerResolverInterface;

final readonly class AccessReviewWorkItemFactory
{
    public function __construct(
        private AccessReviewerResolverInterface $reviewers
    ) {
    }

    public function create(
        AccessReviewCampaign $campaign,
        EffectiveAccessAssignment $assignment
    ): AccessReviewWorkItem {
        return new AccessReviewWorkItem(
            $campaign->id(),
            $assignment,
            $this->reviewers->resolve(
                $campaign,
                $assignment
            ),
            new AccessReviewWorkflowStatus(
                AccessReviewWorkflowStatus::PENDING
            )
        );
    }
}
