<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

final readonly class EffectiveAccessAssignment
{
    public function __construct(
        private AccessAssignment $assignment,
        private Entitlement $entitlement
    ) {
    }

    public function assignment(): AccessAssignment
    {
        return $this->assignment;
    }

    public function entitlement(): Entitlement
    {
        return $this->entitlement;
    }
}
