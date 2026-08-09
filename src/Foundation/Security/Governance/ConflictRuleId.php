<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class ConflictRuleId
{
    public function __construct(private string $value)
    {
        if (
            trim($this->value) === ''
            || strlen($this->value) > 255
        ) {
            throw new InvalidArgumentException(
                'Governance conflict rule id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
