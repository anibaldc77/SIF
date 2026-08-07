<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Permission;

use InvalidArgumentException;

final readonly class PermissionIdentifier
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (
            strlen($normalized) < 3
            || strlen($normalized) > 160
            || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $normalized) !== 1
        ) {
            throw new InvalidArgumentException(
                'Permission identifier must be bounded and use canonical authorization characters.'
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
