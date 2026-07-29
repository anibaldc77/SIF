<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidRequirementIdentifierException;

final readonly class RequirementIdentifier
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if (
            $value === ''
            || strlen($value) > 128
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $value) !== 1
        ) {
            throw new InvalidRequirementIdentifierException(
                sprintf('Invalid requirement identifier "%s".', $value),
            );
        }

        $this->value = $value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
