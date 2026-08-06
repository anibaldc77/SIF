<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Identity;

use Sif\Foundation\Security\Exceptions\InvalidIdentityException;

final readonly class IdentityId
{
    public function __construct(private string $value)
    {
        if ($value === '' || strlen($value) > 255 || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidIdentityException(
                'Identity identifier must be non-empty, contain no control characters and not exceed 255 bytes.'
            );
        }
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
