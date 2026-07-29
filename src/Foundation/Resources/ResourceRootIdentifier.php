<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources;

use Sif\Foundation\Resources\Exceptions\InvalidResourceRootException;

final readonly class ResourceRootIdentifier
{
    private const MAXIMUM_LENGTH = 128;

    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || strlen($value) > self::MAXIMUM_LENGTH) {
            throw new InvalidResourceRootException('Resource root identifiers must contain between 1 and 128 characters.');
        }

        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/D', $value) !== 1) {
            throw new InvalidResourceRootException(sprintf('Resource root identifier "%s" is not portable.', $value));
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
