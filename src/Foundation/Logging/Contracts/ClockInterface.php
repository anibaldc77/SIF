<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogTimestamp;

interface ClockInterface
{
    public function now(): LogTimestamp;
}
