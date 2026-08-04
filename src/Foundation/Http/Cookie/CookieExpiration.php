<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Http\Cookie\Exceptions\InvalidCookieException;

final readonly class CookieExpiration
{
    public function __construct(
        private ?DateTimeImmutable $expires = null,
        private ?int $maxAge = null,
    ) {
        if ($maxAge !== null && $maxAge < 0) {
            throw new InvalidCookieException('Cookie Max-Age must be greater than or equal to zero.');
        }
    }

    public static function removal(): self
    {
        return new self(
            new DateTimeImmutable('1970-01-01 00:00:00', new DateTimeZone('UTC')),
            0,
        );
    }

    public function expires(): ?DateTimeImmutable
    {
        return $this->expires;
    }

    public function maxAge(): ?int
    {
        return $this->maxAge;
    }
}
