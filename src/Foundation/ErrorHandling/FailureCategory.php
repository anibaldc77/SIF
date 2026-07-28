<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureCategoryException;

final readonly class FailureCategory
{
    public const APPLICATION = 'application';
    public const CONFIGURATION = 'configuration';
    public const DEPENDENCY = 'dependency';
    public const INFRASTRUCTURE = 'infrastructure';
    public const SECURITY = 'security';
    public const VALIDATION = 'validation';
    public const UNKNOWN = 'unknown';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, [
            self::APPLICATION,
            self::CONFIGURATION,
            self::DEPENDENCY,
            self::INFRASTRUCTURE,
            self::SECURITY,
            self::VALIDATION,
            self::UNKNOWN,
        ], true)) {
            throw new InvalidFailureCategoryException(sprintf('Invalid FailureCategory value "%s".', $value));
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }

    public static function application(): self { return new self(self::APPLICATION); }

    public static function configuration(): self { return new self(self::CONFIGURATION); }

    public static function dependency(): self { return new self(self::DEPENDENCY); }

    public static function infrastructure(): self { return new self(self::INFRASTRUCTURE); }

    public static function security(): self { return new self(self::SECURITY); }

    public static function validation(): self { return new self(self::VALIDATION); }

    public static function unknown(): self { return new self(self::UNKNOWN); }
}
