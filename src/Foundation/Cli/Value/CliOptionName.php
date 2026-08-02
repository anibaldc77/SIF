<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliOptionNameException;

final readonly class CliOptionName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = ltrim(trim($value), '-');
        if ($value === '' || preg_match('/^[a-z][a-z0-9-]*$/', $value) !== 1) {
            throw new InvalidCliOptionNameException(sprintf('Invalid CLI option name "%s".', $value));
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
