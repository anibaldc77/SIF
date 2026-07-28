<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging;

use Sif\Foundation\Logging\Exceptions\InvalidLogLevelException;

final readonly class LogLevel
{
    /** @var array<string, int> */
    private const PRIORITIES = [
        'debug' => 100,
        'info' => 200,
        'notice' => 250,
        'warning' => 300,
        'error' => 400,
        'critical' => 500,
        'alert' => 550,
        'emergency' => 600,
    ];

    private int $priority;

    public function __construct(private string $value)
    {
        if (!isset(self::PRIORITIES[$value])) {
            throw InvalidLogLevelException::forValue($value);
        }

        $this->priority = self::PRIORITIES[$value];
    }

    public static function debug(): self { return new self('debug'); }
    public static function info(): self { return new self('info'); }
    public static function notice(): self { return new self('notice'); }
    public static function warning(): self { return new self('warning'); }
    public static function error(): self { return new self('error'); }
    public static function critical(): self { return new self('critical'); }
    public static function alert(): self { return new self('alert'); }
    public static function emergency(): self { return new self('emergency'); }

    public function value(): string { return $this->value; }
    public function priority(): int { return $this->priority; }
    public function isAtLeast(self $minimum): bool { return $this->priority >= $minimum->priority; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}
