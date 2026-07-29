<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources;

use Sif\Foundation\Resources\Exceptions\InvalidResourcePriorityException;

final readonly class ResourcePriority
{
    public const DEFAULT = 0;
    public const MINIMUM = -1_000_000;
    public const MAXIMUM = 1_000_000;

    public function __construct(private int $value = self::DEFAULT)
    {
        if ($value < self::MINIMUM || $value > self::MAXIMUM) {
            throw new InvalidResourcePriorityException(sprintf(
                'Resource priority must be between %d and %d.',
                self::MINIMUM,
                self::MAXIMUM,
            ));
        }
    }

    public static function default(): self
    {
        return new self();
    }

    public function value(): int
    {
        return $this->value;
    }

    public function compare(self $other): int
    {
        return $this->value <=> $other->value;
    }
}
