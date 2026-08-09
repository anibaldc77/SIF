<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;
use Sif\Foundation\Security\Exceptions\InvalidAccessReviewWorkflowTransitionException;

final readonly class AccessReviewWorkflowManager
{
    public function start(
        AccessReviewWorkItem $item,
        AccessReviewerId $actor,
        DateTimeImmutable $occurredAt
    ): AccessReviewWorkflowTransition {
        if (
            $item->status()->value()
            !== AccessReviewWorkflowStatus::PENDING
        ) {
            throw new InvalidAccessReviewWorkflowTransitionException(
                'Only pending access review items can enter review.'
            );
        }

        return new AccessReviewWorkflowTransition(
            $item->status(),
            new AccessReviewWorkflowStatus(
                AccessReviewWorkflowStatus::IN_REVIEW
            ),
            $actor,
            $occurredAt
        );
    }

    public function decide(
        AccessReviewWorkItem $item,
        AccessReviewerId $actor,
        DateTimeImmutable $occurredAt
    ): AccessReviewWorkflowTransition {
        if (
            $item->status()->value()
            !== AccessReviewWorkflowStatus::IN_REVIEW
        ) {
            throw new InvalidAccessReviewWorkflowTransitionException(
                'Only in-review access review items can be decided.'
            );
        }

        return new AccessReviewWorkflowTransition(
            $item->status(),
            new AccessReviewWorkflowStatus(
                AccessReviewWorkflowStatus::DECIDED
            ),
            $actor,
            $occurredAt
        );
    }
}
