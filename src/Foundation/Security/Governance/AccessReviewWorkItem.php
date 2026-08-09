<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class AccessReviewWorkItem
{
    public function __construct(
        private AccessReviewCampaignId $campaignId,
        private EffectiveAccessAssignment $effectiveAssignment,
        private AccessReviewerId $reviewerId,
        private AccessReviewWorkflowStatus $status,
        private ?AccessReviewDecision $decision = null
    ) {
    }

    public function campaignId(): AccessReviewCampaignId
    {
        return $this->campaignId;
    }

    public function effectiveAssignment(): EffectiveAccessAssignment
    {
        return $this->effectiveAssignment;
    }

    public function reviewerId(): AccessReviewerId
    {
        return $this->reviewerId;
    }

    public function status(): AccessReviewWorkflowStatus
    {
        return $this->status;
    }

    public function decision(): ?AccessReviewDecision
    {
        return $this->decision;
    }

    public function decided(): bool
    {
        return $this->decision !== null
            && $this->status->value()
            === AccessReviewWorkflowStatus::DECIDED;
    }
}
