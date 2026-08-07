<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use Sif\Foundation\Security\Exceptions\InvalidTrustedDeviceGrantException;

final readonly class TrustedDeviceGrantId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (
            strlen($normalized) < 16
            || strlen($normalized) > 128
            || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $normalized) !== 1
        ) {
            throw new InvalidTrustedDeviceGrantException(
                'Trusted-device grant identifier must be bounded and transport-safe.'
            );
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}
