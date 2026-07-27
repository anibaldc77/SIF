<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Context;

use DateTimeImmutable;
use Sif\Foundation\Contracts\ClockInterface;

final readonly class FrozenClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $instant)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->instant;
    }
}
