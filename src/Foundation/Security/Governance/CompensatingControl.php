<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class CompensatingControl
{
    public function __construct(
        private CompensatingControlId $id,
        private string $name,
        private string $description,
        private GovernanceRiskLevel $residualRisk
    ) {
    }

    public function id(): CompensatingControlId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function residualRisk(): GovernanceRiskLevel
    {
        return $this->residualRisk;
    }
}
