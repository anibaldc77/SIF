<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Patch;

use InvalidArgumentException;

final readonly class ScimPatchOperationType
{
    public const ADD = 'add';
    public const REMOVE = 'remove';
    public const REPLACE = 'replace';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [self::ADD, self::REMOVE, self::REPLACE],
            true
        )) {
            throw new InvalidArgumentException(
                'SCIM PATCH operation type is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
