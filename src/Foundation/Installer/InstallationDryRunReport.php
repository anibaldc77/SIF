<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidInstallationDryRunReportException;
use Sif\Foundation\Installer\Mutations\MutationPlan;

final readonly class InstallationDryRunReport
{
    public function __construct(
        private InstallationRequest $request,
        private RequirementAssessmentReport $requirements,
        private InstallationStepPlan $steps,
        private MutationPlan $mutations,
    ) {
        if ($mutations->fingerprint() === '') {
            throw new InvalidInstallationDryRunReportException('Dry-run reports require a compiled mutation plan.');
        }
    }

    public function request(): InstallationRequest { return $this->request; }
    public function requirements(): RequirementAssessmentReport { return $this->requirements; }
    public function steps(): InstallationStepPlan { return $this->steps; }
    public function mutations(): MutationPlan { return $this->mutations; }
    public function planFingerprint(): string { return $this->mutations->fingerprint(); }
    public function executable(): bool { return $this->requirements->canProceed(); }
    public function hasWarnings(): bool { return $this->requirements->hasWarnings(); }

    /** @return array<string,mixed> */
    public function summary(): array
    {
        return [
            'request' => $this->request->summary(),
            'requirements' => $this->requirements->summary(),
            'steps' => $this->steps->summary(),
            'mutations' => $this->mutations->summary(),
            'plan_fingerprint' => $this->mutations->fingerprint(),
            'executable' => $this->executable(),
            'has_warnings' => $this->hasWarnings(),
        ];
    }
}
