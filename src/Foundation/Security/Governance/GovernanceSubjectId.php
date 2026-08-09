<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class GovernanceSubjectId
{
    public function __construct(private string $value)
    {
        if (
            trim($this->value) === ''
            || strlen($this->value) > 255
        ) {
            throw new InvalidArgumentException(
                'Governance subject id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
