<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Contracts\RetryDelayStrategyInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidRetryGuidanceException;

final readonly class RetryGuidance
{
    public function __construct(
        private int $attempt,
        private int $maximumAttempts,
        private int $delayMilliseconds,
    ) {
        if ($attempt < 1 || $maximumAttempts < 1 || $attempt > $maximumAttempts || $delayMilliseconds < 0) {
            throw new InvalidRetryGuidanceException('Retry guidance is invalid.');
        }
    }

    public static function forAttempt(
        int $attempt,
        int $maximumAttempts,
        RetryDelayStrategyInterface $strategy,
    ): self {
        return new self($attempt, $maximumAttempts, $strategy->delayMilliseconds($attempt));
    }

    public function attempt(): int { return $this->attempt; }
    public function maximumAttempts(): int { return $this->maximumAttempts; }
    public function delayMilliseconds(): int { return $this->delayMilliseconds; }
    public function hasRemainingAttempts(): bool { return $this->attempt < $this->maximumAttempts; }

    /** @return array{attempt:int,maximum_attempts:int,delay_milliseconds:int,remaining:bool} */
    public function summary(): array
    {
        return [
            'attempt' => $this->attempt,
            'maximum_attempts' => $this->maximumAttempts,
            'delay_milliseconds' => $this->delayMilliseconds,
            'remaining' => $this->hasRemainingAttempts(),
        ];
    }
}
