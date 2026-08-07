<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Role;

use InvalidArgumentException;

final readonly class RoleIdentifier
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (
            strlen($normalized) < 2
            || strlen($normalized) > 120
            || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $normalized) !== 1
        ) {
            throw new InvalidArgumentException(
                'Role identifier must be bounded and use canonical authorization characters.'
            );
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}
