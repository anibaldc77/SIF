<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceLifecycleStatus
{
    public const PENDING = 'pending';
    public const ISSUED = 'issued';
    public const DELIVERED = 'delivered';
    public const ACCEPTED = 'accepted';
    public const FAILED = 'failed';
    public const EXPIRED = 'expired';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::PENDING,
            self::ISSUED,
            self::DELIVERED,
            self::ACCEPTED,
            self::FAILED,
            self::EXPIRED,
        ], true)) {
            throw new InvalidArgumentException(
                'Credential issuance lifecycle status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
