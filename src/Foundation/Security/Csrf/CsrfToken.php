<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

use Sif\Foundation\Security\Csrf\Exceptions\CsrfException;

final readonly class CsrfToken
{
    public function __construct(private string $value)
    {
        if (strlen($value) < 32 || strlen($value) > 128 || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            throw new CsrfException('CSRF tokens must be opaque Base64URL values between 32 and 128 characters.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
