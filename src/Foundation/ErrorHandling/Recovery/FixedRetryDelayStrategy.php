<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Contracts\RetryDelayStrategyInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidRetryGuidanceException;

final readonly class FixedRetryDelayStrategy implements RetryDelayStrategyInterface
{
    public function __construct(private int $delayMilliseconds)
    {
        if ($delayMilliseconds < 0) {
            throw new InvalidRetryGuidanceException('Retry delay must be zero or greater.');
        }
    }

    public function delayMilliseconds(int $attempt): int
    {
        if ($attempt < 1) {
            throw new InvalidRetryGuidanceException('Retry attempt must be one or greater.');
        }
        return $this->delayMilliseconds;
    }
}
