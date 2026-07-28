<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Logging\Contracts\ClockInterface;
use Sif\Foundation\Logging\LogTimestamp;

final class SystemClock implements ClockInterface
{
    public function now(): LogTimestamp
    {
        return new LogTimestamp(new DateTimeImmutable('now', new DateTimeZone('UTC')));
    }
}
