<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

final readonly class SkeletonGenerationEntry
{
    public function __construct(
        private SkeletonArtifact $artifact,
        private SkeletonGenerationAction $action,
        private ?string $currentFingerprint = null,
        private ?string $reason = null,
    ) {
    }

    public function artifact(): SkeletonArtifact
    {
        return $this->artifact;
    }

    public function action(): SkeletonGenerationAction
    {
        return $this->action;
    }

    public function currentFingerprint(): ?string
    {
        return $this->currentFingerprint;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    /** @return array<string, string|null> */
    public function summary(): array
    {
        return [
            'path' => $this->artifact->path()->path()->value(),
            'type' => $this->artifact->type()->value,
            'action' => $this->action->value,
            'target_fingerprint' => $this->artifact->fingerprint(),
            'current_fingerprint' => $this->currentFingerprint,
            'reason' => $this->reason,
        ];
    }
}
