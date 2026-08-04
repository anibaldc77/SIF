<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

use Sif\Foundation\Http\Cookie\Exceptions\InvalidCookieException;

final readonly class CookieValue
{
    public function __construct(private string $value)
    {
        if (preg_match('/[^\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]/', $value) === 1) {
            throw new InvalidCookieException('Cookie values must contain only RFC 6265 cookie-octets.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
