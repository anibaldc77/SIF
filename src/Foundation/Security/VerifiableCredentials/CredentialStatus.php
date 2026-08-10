<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class CredentialStatus
{
    public const VALID = 'valid';
    public const SUSPENDED = 'suspended';
    public const REVOKED = 'revoked';
    public const UNKNOWN = 'unknown';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::VALID,
            self::SUSPENDED,
            self::REVOKED,
            self::UNKNOWN,
        ], true)) {
            throw new InvalidArgumentException(
                'Credential status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
