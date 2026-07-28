<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Clock;

use Sif\Foundation\Logging\Contracts\ClockInterface;
use Sif\Foundation\Logging\LogTimestamp;

final readonly class FrozenClock implements ClockInterface
{
    public function __construct(private LogTimestamp $timestamp)
    {
    }

    public function now(): LogTimestamp
    {
        return $this->timestamp;
    }
}
