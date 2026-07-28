<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureSeverityException;

final readonly class FailureSeverity
{
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const CRITICAL = 'critical';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, [
            self::DEBUG,
            self::INFO,
            self::WARNING,
            self::ERROR,
            self::CRITICAL,
        ], true)) {
            throw new InvalidFailureSeverityException(sprintf('Invalid FailureSeverity value "%s".', $value));
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }

    public static function debug(): self { return new self(self::DEBUG); }

    public static function info(): self { return new self(self::INFO); }

    public static function warning(): self { return new self(self::WARNING); }

    public static function error(): self { return new self(self::ERROR); }

    public static function critical(): self { return new self(self::CRITICAL); }

    public function priority(): int
    {
        return match ($this->value) {
            self::DEBUG => 100,
            self::INFO => 200,
            self::WARNING => 300,
            self::ERROR => 400,
            self::CRITICAL => 500,
            default => throw new \LogicException('Unsupported failure severity.'),
        };
    }

    public function isAtLeast(self $other): bool
    {
        return $this->priority() >= $other->priority();
    }

}
