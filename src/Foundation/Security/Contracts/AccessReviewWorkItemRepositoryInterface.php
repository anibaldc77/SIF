<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessReviewCampaignId;
use Sif\Foundation\Security\Governance\AccessReviewWorkItem;

interface AccessReviewWorkItemRepositoryInterface
{
    public function save(AccessReviewWorkItem $item): void;

    /**
     * @return list<AccessReviewWorkItem>
     */
    public function forCampaign(
        AccessReviewCampaignId $campaignId
    ): array;
}
