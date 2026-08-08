<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Patch;

use InvalidArgumentException;

final readonly class ScimPatchPath
{
    public function __construct(private string $value)
    {
        $value = trim($this->value);

        if (
            $value === ''
            || strlen($value) > 2048
            || preg_match('/[\x00-\x1F\x7F]/', $value) === 1
        ) {
            throw new InvalidArgumentException(
                'SCIM PATCH path is invalid.'
            );
        }
    }

    public function value(): string
    {
        return trim($this->value);
    }
}
