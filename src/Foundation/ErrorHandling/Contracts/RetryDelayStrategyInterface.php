<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

interface RetryDelayStrategyInterface
{
    /** Attempt numbers are one-based. */
    public function delayMilliseconds(int $attempt): int;
}
