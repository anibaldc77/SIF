<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Recovery;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidRecoveryActionException;

final readonly class RecoveryAction
{
    public const CONTINUE = 'continue';
    public const DEGRADE = 'degrade';
    public const RETRY = 'retry';
    public const ABORT = 'abort';
    public const RETHROW = 'rethrow';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, [
            self::CONTINUE,
            self::DEGRADE,
            self::RETRY,
            self::ABORT,
            self::RETHROW,
        ], true)) {
            throw new InvalidRecoveryActionException(sprintf('Invalid RecoveryAction value "%s".', $value));
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function isRetry(): bool { return $this->value === self::RETRY; }

    public static function continue(): self { return new self(self::CONTINUE); }
    public static function degrade(): self { return new self(self::DEGRADE); }
    public static function retry(): self { return new self(self::RETRY); }
    public static function abort(): self { return new self(self::ABORT); }
    public static function rethrow(): self { return new self(self::RETHROW); }
}
