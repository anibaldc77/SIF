<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Contracts\RecoveryPolicyInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidRecoveryPolicyException;
use Sif\Foundation\ErrorHandling\FailureDisposition;

final readonly class DispositionRecoveryPolicy implements RecoveryPolicyInterface
{
    public function __construct(
        private string $name,
        private FailureDisposition $disposition,
        private RecoveryAction $action,
    ) {
        if (preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/', $name) !== 1 || $action->isRetry()) {
            throw new InvalidRecoveryPolicyException('Disposition recovery policy is invalid.');
        }
    }

    public function name(): string { return $this->name; }

    public function decide(ThrowableClassification $classification, int $attempt): ?RecoveryDecision
    {
        if ($attempt < 1) {
            throw new InvalidRecoveryPolicyException('Recovery attempt must be one or greater.');
        }
        if ($classification->disposition()->value() !== $this->disposition->value()) {
            return null;
        }
        return new RecoveryDecision($this->action, $this->name);
    }
}
