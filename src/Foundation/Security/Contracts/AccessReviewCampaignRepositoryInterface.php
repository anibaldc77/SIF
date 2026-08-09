<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessReviewCampaign;
use Sif\Foundation\Security\Governance\AccessReviewCampaignId;

interface AccessReviewCampaignRepositoryInterface
{
    public function find(
        AccessReviewCampaignId $id
    ): ?AccessReviewCampaign;

    public function save(AccessReviewCampaign $campaign): void;
}
