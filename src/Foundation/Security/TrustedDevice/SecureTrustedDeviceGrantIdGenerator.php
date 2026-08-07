<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use Sif\Foundation\Security\Contracts\TrustedDeviceGrantIdGeneratorInterface;

final readonly class SecureTrustedDeviceGrantIdGenerator implements TrustedDeviceGrantIdGeneratorInterface
{
    public function __construct(private int $bytes = 16)
    {
        if ($bytes < 8 || $bytes > 64) {
            throw new \InvalidArgumentException(
                'Trusted-device grant identifier entropy must be between 64 and 512 bits.'
            );
        }
    }

    public function generate(): TrustedDeviceGrantId
    {
        $bytes = max(1, $this->bytes);

        return new TrustedDeviceGrantId(
            bin2hex(random_bytes($bytes))
        );
    }
}
