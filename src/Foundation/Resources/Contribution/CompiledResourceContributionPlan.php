<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contribution;

use Sif\Foundation\Resources\ResourceDescriptor;

final readonly class CompiledResourceContributionPlan
{
    /**
     * @param list<PlannedResourceContribution> $effectiveContributions
     * @param list<ResourceOverrideDecision> $overrideDecisions
     */
    public function __construct(
        private array $effectiveContributions,
        private array $overrideDecisions,
    ) {
    }

    /** @return list<PlannedResourceContribution> */
    public function effectiveContributions(): array { return $this->effectiveContributions; }

    /** @return list<ResourceDescriptor> */
    public function resources(): array
    {
        return array_map(
            static fn (PlannedResourceContribution $planned): ResourceDescriptor => $planned->contribution()->descriptor(),
            $this->effectiveContributions,
        );
    }

    /** @return list<ResourceOverrideDecision> */
    public function overrideDecisions(): array { return $this->overrideDecisions; }
    public function count(): int { return count($this->effectiveContributions); }
}
