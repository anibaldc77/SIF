<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class AccessReviewCampaignId
{
    public function __construct(private string $value)
    {
        if (
            trim($this->value) === ''
            || strlen($this->value) > 255
        ) {
            throw new InvalidArgumentException(
                'Access review campaign id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
