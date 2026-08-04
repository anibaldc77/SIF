<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

use DateTimeZone;

final readonly class CookieSerializer
{
    public function serialize(Cookie $cookie): string
    {
        $parts = [sprintf('%s=%s', $cookie->name()->value(), $cookie->value()->value())];
        $expiration = $cookie->expiration();

        if ($expiration->expires() !== null) {
            $parts[] = 'Expires=' . $expiration->expires()
                ->setTimezone(new DateTimeZone('GMT'))
                ->format('D, d M Y H:i:s \G\M\T');
        }
        if ($expiration->maxAge() !== null) {
            $parts[] = 'Max-Age=' . $expiration->maxAge();
        }
        if ($cookie->domain() !== null) {
            $parts[] = 'Domain=' . $cookie->domain();
        }

        $parts[] = 'Path=' . $cookie->path();

        if ($cookie->secure()) {
            $parts[] = 'Secure';
        }
        if ($cookie->httpOnly()) {
            $parts[] = 'HttpOnly';
        }

        $parts[] = 'SameSite=' . $cookie->sameSite()->value;

        return implode('; ', $parts);
    }
}
