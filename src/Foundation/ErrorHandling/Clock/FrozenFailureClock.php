<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Clock;

use DateTimeImmutable;
use Sif\Foundation\ErrorHandling\Contracts\FailureClockInterface;

final readonly class FrozenFailureClock implements FailureClockInterface
{
    public function __construct(private DateTimeImmutable $instant)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->instant;
    }
}
