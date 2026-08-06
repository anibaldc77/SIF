<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use Sif\Foundation\Security\Exceptions\InvalidAuthenticationRequestException;

final readonly class AuthenticationRequestId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if ($normalized === '' || strlen($normalized) > 128 || preg_match('/[\x00-\x1F\x7F]/', $normalized) === 1) {
            throw new InvalidAuthenticationRequestException('Authentication request identifier is invalid.');
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }
}
