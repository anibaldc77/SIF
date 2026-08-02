<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliArgumentNameException;

final readonly class CliArgumentName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^[a-z][a-z0-9-]*$/', $value) !== 1) {
            throw new InvalidCliArgumentNameException(sprintf('Invalid CLI argument name "%s".', $value));
        }

        $this->value = $value;
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
