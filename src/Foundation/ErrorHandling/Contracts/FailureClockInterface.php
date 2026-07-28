<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use DateTimeImmutable;

interface FailureClockInterface
{
    public function now(): DateTimeImmutable;
}
