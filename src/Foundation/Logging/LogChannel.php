<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging;

use Sif\Foundation\Logging\Exceptions\InvalidLogChannelException;

final readonly class LogChannel
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/D', $value) !== 1) {
            throw InvalidLogChannelException::forValue($value);
        }
    }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}
