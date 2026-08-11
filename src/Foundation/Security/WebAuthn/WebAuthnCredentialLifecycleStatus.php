<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnCredentialLifecycleStatus
{
    public const ACTIVE = 'active';
    public const SUSPENDED = 'suspended';
    public const REVOKED = 'revoked';
    public const RETIRED = 'retired';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::ACTIVE,
            self::SUSPENDED,
            self::REVOKED,
            self::RETIRED,
        ], true)) {
            throw new InvalidArgumentException(
                'WebAuthn credential lifecycle status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
