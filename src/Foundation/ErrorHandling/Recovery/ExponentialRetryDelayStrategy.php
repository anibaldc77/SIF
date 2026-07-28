<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Contracts\RetryDelayStrategyInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidRetryGuidanceException;

final readonly class ExponentialRetryDelayStrategy implements RetryDelayStrategyInterface
{
    public function __construct(
        private int $initialDelayMilliseconds,
        private int $maximumDelayMilliseconds,
    ) {
        if ($initialDelayMilliseconds < 0 || $maximumDelayMilliseconds < $initialDelayMilliseconds) {
            throw new InvalidRetryGuidanceException('Retry delay bounds are invalid.');
        }
    }

    public function delayMilliseconds(int $attempt): int
    {
        if ($attempt < 1) {
            throw new InvalidRetryGuidanceException('Retry attempt must be one or greater.');
        }
        $factor = 2 ** ($attempt - 1);
        $delay = $this->initialDelayMilliseconds * $factor;
        return min($delay, $this->maximumDelayMilliseconds);
    }
}
