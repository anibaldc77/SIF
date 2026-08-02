<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\FirstRun;

use Sif\Foundation\ApplicationSkeleton\Validation\ApplicationSkeletonValidationReport;
use Sif\Foundation\ApplicationSkeleton\Value\FirstRunState;

final readonly class ApplicationFirstRunReport
{
    public function __construct(
        private FirstRunState $state,
        private string $planFingerprint,
        private bool $executed,
        private ApplicationSkeletonValidationReport $validation,
    ) {
    }

    public function state(): FirstRunState
    {
        return $this->state;
    }

    public function completed(): bool
    {
        return $this->state === FirstRunState::Completed;
    }

    /** @return array{state: string, plan_fingerprint: string, executed: bool, validation: array{valid: bool, issue_count: int, issues: list<array{code: string, message: string}>}} */
    public function summary(): array
    {
        return [
            'state' => $this->state->value,
            'plan_fingerprint' => $this->planFingerprint,
            'executed' => $this->executed,
            'validation' => $this->validation->summary(),
        ];
    }
}
