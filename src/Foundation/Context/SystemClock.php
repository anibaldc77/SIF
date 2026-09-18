<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Contracts\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
