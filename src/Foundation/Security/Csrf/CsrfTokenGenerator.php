<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

final readonly class CsrfTokenGenerator
{
    public function __construct(private int $entropyBytes = 32)
    {
        if ($entropyBytes < 24) {
            throw new \InvalidArgumentException('CSRF token entropy must be at least 24 bytes.');
        }
    }

    public function generate(): CsrfToken
    {
        return new CsrfToken(rtrim(strtr(base64_encode(random_bytes(max(1, $this->entropyBytes))), '+/', '-_'), '='));
    }
}
