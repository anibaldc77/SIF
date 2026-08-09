<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessReviewItem;

interface AccessReviewDecisionPublisherInterface
{
    public function publish(AccessReviewItem $item): void;
}
