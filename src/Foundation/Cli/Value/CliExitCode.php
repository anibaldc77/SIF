<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliExitCodeException;

final readonly class CliExitCode
{
    /** @var array<int, string> */
    private const LABELS = [
        0 => 'success',
        1 => 'execution-failure',
        2 => 'invalid-usage',
        3 => 'command-not-found',
        4 => 'validation-failure',
        5 => 'not-authorized',
        6 => 'requirements-not-satisfied',
        7 => 'partial-or-compensated',
        8 => 'internal-failure',
    ];

    public function __construct(private int $value)
    {
        if (!array_key_exists($this->value, self::LABELS)) {
            throw new InvalidCliExitCodeException(sprintf('Invalid governed CLI exit code "%d".', $this->value));
        }
    }

    public static function success(): self { return new self(0); }
    public static function executionFailure(): self { return new self(1); }
    public static function invalidUsage(): self { return new self(2); }
    public static function commandNotFound(): self { return new self(3); }
    public static function validationFailure(): self { return new self(4); }
    public static function notAuthorized(): self { return new self(5); }
    public static function requirementsNotSatisfied(): self { return new self(6); }
    public static function partialOrCompensated(): self { return new self(7); }
    public static function internalFailure(): self { return new self(8); }
    public function value(): int { return $this->value; }
    public function label(): string { return self::LABELS[$this->value]; }
    public function successful(): bool { return $this->value === 0; }
}
