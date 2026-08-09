<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\AccessReviewWorkItem;

interface AccessReviewEscalationResolverInterface
{
    public function resolve(
        AccessReviewWorkItem $item
    ): AccessReviewerId;
}
