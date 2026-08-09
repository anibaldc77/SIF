<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class GovernanceExceptionStatus
{
    public const REQUESTED = 'requested';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const EXPIRED = 'expired';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [
                self::REQUESTED,
                self::APPROVED,
                self::REJECTED,
                self::EXPIRED,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Governance exception status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
