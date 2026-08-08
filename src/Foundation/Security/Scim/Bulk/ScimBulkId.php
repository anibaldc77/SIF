<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

use InvalidArgumentException;

final readonly class ScimBulkId
{
    public function __construct(private string $value)
    {
        if (
            $this->value === ''
            || strlen($this->value) > 255
            || preg_match('/^[A-Za-z0-9._:-]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'SCIM bulkId is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function reference(): string
    {
        return 'bulkId:' . $this->value;
    }
}
