<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use DateTimeImmutable;

/** Supplies the current instant without coupling Context to the system clock. */
interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
