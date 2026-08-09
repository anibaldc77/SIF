<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessReviewCampaign;
use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\EffectiveAccessAssignment;

interface AccessReviewerResolverInterface
{
    public function resolve(
        AccessReviewCampaign $campaign,
        EffectiveAccessAssignment $assignment
    ): AccessReviewerId;
}
