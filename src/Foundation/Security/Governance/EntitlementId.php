<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;

final readonly class EntitlementId
{
    public function __construct(private string $value)
    {
        if (
            trim($this->value) === ''
            || strlen($this->value) > 255
        ) {
            throw new InvalidArgumentException(
                'Entitlement id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
