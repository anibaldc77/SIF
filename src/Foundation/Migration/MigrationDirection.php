<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationDirectionException;

final readonly class MigrationDirection
{
    private const UP = 'up';
    private const DOWN = 'down';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!in_array($value, [self::UP, self::DOWN], true)) {
            throw new InvalidMigrationDirectionException('Migration direction must be "up" or "down".');
        }

        $this->value = $value;
    }

    public static function up(): self
    {
        return new self(self::UP);
    }

    public static function down(): self
    {
        return new self(self::DOWN);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isUp(): bool
    {
        return $this->value === self::UP;
    }

    public function isDown(): bool
    {
        return $this->value === self::DOWN;
    }
}
