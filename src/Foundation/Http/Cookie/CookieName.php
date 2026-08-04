<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

use Sif\Foundation\Http\Cookie\Exceptions\InvalidCookieException;

final readonly class CookieName
{
    public function __construct(private string $value)
    {
        if ($value === '' || preg_match("/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/", $value) !== 1) {
            throw new InvalidCookieException(sprintf('Invalid cookie name "%s".', $value));
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function hasSecurePrefix(): bool
    {
        return str_starts_with($this->value, '__Secure-');
    }

    public function hasHostPrefix(): bool
    {
        return str_starts_with($this->value, '__Host-');
    }
}
