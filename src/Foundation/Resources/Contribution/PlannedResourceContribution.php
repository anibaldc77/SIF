<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contribution;

use Sif\Foundation\Resources\Exceptions\InvalidResourceContributionOrderException;

final readonly class PlannedResourceContribution
{
    public function __construct(
        private ModuleResourceContribution $contribution,
        private int $contributionOrder,
    ) {
        if ($contributionOrder < 0) {
            throw new InvalidResourceContributionOrderException('Resource contribution order cannot be negative.');
        }
    }

    public function contribution(): ModuleResourceContribution { return $this->contribution; }
    public function contributionOrder(): int { return $this->contributionOrder; }
}
