<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthDeviceAuthorizationStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const DENIED = 'denied';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [self::PENDING, self::APPROVED, self::DENIED],
            true
        )) {
            throw new InvalidArgumentException(
                'OAuth device authorization status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
