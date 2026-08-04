<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

use Sif\Foundation\Session\Exceptions\SessionException;

final readonly class SessionId
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[A-Za-z0-9_-]{32,128}$/D', $value) !== 1) {
            throw new SessionException('Session identifier must be an opaque base64url-compatible value between 32 and 128 characters.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
