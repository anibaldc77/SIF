<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessReviewerId;
use Sif\Foundation\Security\Governance\GovernanceException;

interface GovernanceExceptionApproverResolverInterface
{
    public function resolve(
        GovernanceException $exception
    ): AccessReviewerId;
}
