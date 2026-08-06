<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

final readonly class RecoveryChallengeConsumptionResult
{
    private function __construct(
        private RecoveryChallengeConsumptionStatus $status,
        private ?RecoveryChallengeRecord $record
    ) {
    }

    public static function consumed(RecoveryChallengeRecord $record): self
    {
        return new self(RecoveryChallengeConsumptionStatus::Consumed, $record);
    }

    public static function rejected(RecoveryChallengeConsumptionStatus $status): self
    {
        return new self($status, null);
    }

    public function status(): RecoveryChallengeConsumptionStatus
    {
        return $this->status;
    }

    public function record(): ?RecoveryChallengeRecord
    {
        return $this->record;
    }

    public function isConsumed(): bool
    {
        return $this->status === RecoveryChallengeConsumptionStatus::Consumed;
    }
}
