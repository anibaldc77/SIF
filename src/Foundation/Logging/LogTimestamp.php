<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging;

use DateTimeImmutable;
use DateTimeZone;

final readonly class LogTimestamp
{
    private DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value->setTimezone(new DateTimeZone('UTC'));
    }

    public function value(): DateTimeImmutable { return $this->value; }
    public function toCanonicalString(): string { return $this->value->format('Y-m-d\TH:i:s.u\Z'); }
    public function equals(self $other): bool { return $this->toCanonicalString() === $other->toCanonicalString(); }
    public function __toString(): string { return $this->toCanonicalString(); }
}
