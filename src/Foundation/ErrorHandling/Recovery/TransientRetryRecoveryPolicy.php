<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Contracts\RecoveryPolicyInterface;
use Sif\Foundation\ErrorHandling\Contracts\RetryDelayStrategyInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidRecoveryPolicyException;
use Sif\Foundation\ErrorHandling\FailureDisposition;

final readonly class TransientRetryRecoveryPolicy implements RecoveryPolicyInterface
{
    public function __construct(
        private string $name,
        private int $maximumAttempts,
        private RetryDelayStrategyInterface $delayStrategy,
        private RecoveryAction $exhaustedAction = new RecoveryAction(RecoveryAction::RETHROW),
    ) {
        if (preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/', $name) !== 1
            || $maximumAttempts < 1
            || $exhaustedAction->isRetry()) {
            throw new InvalidRecoveryPolicyException('Transient retry recovery policy is invalid.');
        }
    }

    public function name(): string { return $this->name; }

    public function decide(ThrowableClassification $classification, int $attempt): ?RecoveryDecision
    {
        if ($attempt < 1) {
            throw new InvalidRecoveryPolicyException('Recovery attempt must be one or greater.');
        }
        if ($classification->disposition()->value() !== FailureDisposition::TRANSIENT) {
            return null;
        }
        if ($attempt >= $this->maximumAttempts) {
            return new RecoveryDecision($this->exhaustedAction, $this->name);
        }
        return new RecoveryDecision(
            RecoveryAction::retry(),
            $this->name,
            RetryGuidance::forAttempt($attempt, $this->maximumAttempts, $this->delayStrategy),
        );
    }
}
