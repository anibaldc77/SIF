<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

use InvalidArgumentException;

final readonly class ScimBulkOperationType
{
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [self::POST, self::PUT, self::PATCH, self::DELETE],
            true
        )) {
            throw new InvalidArgumentException(
                'SCIM Bulk operation method is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
