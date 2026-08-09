<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class AccessReviewDecision
{
    public const APPROVE = 'approve';
    public const REVOKE = 'revoke';
    public const DEFER = 'defer';

    public function __construct(
        private string $value,
        private ?string $reason = null
    ) {
        if (!in_array(
            $this->value,
            [self::APPROVE, self::REVOKE, self::DEFER],
            true
        )) {
            throw new InvalidArgumentException(
                'Access review decision is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
