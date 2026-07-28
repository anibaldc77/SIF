<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use DateTimeImmutable;
use DateTimeZone;

final readonly class FailureTimestamp
{
    private DateTimeImmutable $instant;

    public function __construct(DateTimeImmutable $instant)
    {
        $this->instant = $instant->setTimezone(new DateTimeZone('UTC'));
    }

    public function instant(): DateTimeImmutable { return $this->instant; }
    public function canonical(): string { return $this->instant->format('Y-m-d\\TH:i:s.u\\Z'); }
    public function __toString(): string { return $this->canonical(); }
}
