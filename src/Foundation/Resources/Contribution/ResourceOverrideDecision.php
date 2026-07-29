<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contribution;

final readonly class ResourceOverrideDecision
{
    public function __construct(
        private PlannedResourceContribution $winner,
        private PlannedResourceContribution $replaced,
    ) {
    }

    public function winner(): PlannedResourceContribution { return $this->winner; }
    public function replaced(): PlannedResourceContribution { return $this->replaced; }
    public function qualifiedIdentifier(): string { return $this->winner->contribution()->descriptor()->qualifiedIdentifier(); }
}
