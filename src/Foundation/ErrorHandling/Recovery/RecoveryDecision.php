<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidRecoveryDecisionException;

final readonly class RecoveryDecision
{
    public function __construct(
        private RecoveryAction $action,
        private string $policy,
        private ?RetryGuidance $retryGuidance = null,
        private bool $fallback = false,
    ) {
        if (preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/', $policy) !== 1) {
            throw new InvalidRecoveryDecisionException(sprintf('Invalid recovery policy name "%s".', $policy));
        }
        if ($action->isRetry() !== ($retryGuidance !== null)) {
            throw new InvalidRecoveryDecisionException('Retry guidance must exist only for retry decisions.');
        }
    }

    public function action(): RecoveryAction { return $this->action; }
    public function policy(): string { return $this->policy; }
    public function retryGuidance(): ?RetryGuidance { return $this->retryGuidance; }
    public function isFallback(): bool { return $this->fallback; }

    /** @return array{action:string,policy:string,fallback:bool,retry:?array{attempt:int,maximum_attempts:int,delay_milliseconds:int,remaining:bool}} */
    public function summary(): array
    {
        return [
            'action' => $this->action->value(),
            'policy' => $this->policy,
            'fallback' => $this->fallback,
            'retry' => $this->retryGuidance?->summary(),
        ];
    }

    public static function fallbackRethrow(): self
    {
        return new self(RecoveryAction::rethrow(), 'fallback.rethrow', null, true);
    }
}
