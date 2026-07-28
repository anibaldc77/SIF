<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\ErrorHandling\Contracts\FailureClockInterface;

final class SystemFailureClock implements FailureClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
