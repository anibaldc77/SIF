<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class AccessReviewWorkflowStatus
{
    public const PENDING = 'pending';
    public const IN_REVIEW = 'in-review';
    public const DECIDED = 'decided';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [self::PENDING, self::IN_REVIEW, self::DECIDED],
            true
        )) {
            throw new InvalidArgumentException(
                'Access review workflow status is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
