<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class SegregationOfDutiesRule
{
    public function __construct(
        private ConflictRuleId $id,
        private EntitlementId $left,
        private EntitlementId $right,
        private GovernanceRiskLevel $risk,
        private string $description
    ) {
        if ($this->left->value() === $this->right->value()) {
            throw new InvalidArgumentException(
                'Segregation of duties rule requires distinct entitlements.'
            );
        }
    }

    public function id(): ConflictRuleId
    {
        return $this->id;
    }

    public function left(): EntitlementId
    {
        return $this->left;
    }

    public function right(): EntitlementId
    {
        return $this->right;
    }

    public function risk(): GovernanceRiskLevel
    {
        return $this->risk;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function conflicts(
        EntitlementId $first,
        EntitlementId $second
    ): bool {
        return (
            $this->left->value() === $first->value()
            && $this->right->value() === $second->value()
        ) || (
            $this->left->value() === $second->value()
            && $this->right->value() === $first->value()
        );
    }
}
