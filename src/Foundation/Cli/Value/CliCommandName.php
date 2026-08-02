<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliCommandNameException;

final readonly class CliCommandName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^[a-z][a-z0-9-]*(?::[a-z][a-z0-9-]*)+$/', $value) !== 1) {
            throw new InvalidCliCommandNameException(sprintf('Invalid CLI command name "%s".', $value));
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
