<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class AccessReviewCampaignStatus
{
    public const DRAFT = 'draft';
    public const ACTIVE = 'active';
    public const CLOSED = 'closed';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [self::DRAFT, self::ACTIVE, self::CLOSED],
            true
        )) {
            throw new InvalidArgumentException(
                'Access review campaign status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
