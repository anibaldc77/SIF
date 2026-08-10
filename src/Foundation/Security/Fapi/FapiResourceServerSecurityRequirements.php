<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiResourceServerSecurityRequirements
{
    public function __construct(
        private bool $requireSenderConstraint = true,
        private bool $requireExactResourceMatch = true,
        private bool $requireActiveToken = true,
        private bool $rejectBearerDowngrade = true,
        private bool $requireAudienceValidation = true
    ) {
    }

    public function requireSenderConstraint(): bool
    {
        return $this->requireSenderConstraint;
    }

    public function requireExactResourceMatch(): bool
    {
        return $this->requireExactResourceMatch;
    }

    public function requireActiveToken(): bool
    {
        return $this->requireActiveToken;
    }

    public function rejectBearerDowngrade(): bool
    {
        return $this->rejectBearerDowngrade;
    }

    public function requireAudienceValidation(): bool
    {
        return $this->requireAudienceValidation;
    }
}
