<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliInvocationException;

final readonly class CliVerbosity
{
    private const VALUES = ['quiet', 'normal', 'verbose', 'debug'];

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, self::VALUES, true)) {
            throw new InvalidCliInvocationException(sprintf('Invalid CLI verbosity "%s".', $value));
        }

        $this->value = $value;
    }

    public static function normal(): self { return new self('normal'); }
    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
