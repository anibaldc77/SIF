<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureDispositionException;

final readonly class FailureDisposition
{
    public const TRANSIENT = 'transient';
    public const PERMANENT = 'permanent';
    public const INVALID = 'invalid';
    public const UNKNOWN = 'unknown';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, [
            self::TRANSIENT,
            self::PERMANENT,
            self::INVALID,
            self::UNKNOWN,
        ], true)) {
            throw new InvalidFailureDispositionException(sprintf('Invalid FailureDisposition value "%s".', $value));
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }

    public static function transient(): self { return new self(self::TRANSIENT); }

    public static function permanent(): self { return new self(self::PERMANENT); }

    public static function invalid(): self { return new self(self::INVALID); }

    public static function unknown(): self { return new self(self::UNKNOWN); }
}
