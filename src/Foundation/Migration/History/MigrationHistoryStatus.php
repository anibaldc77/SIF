<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\History;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationHistoryStatusException;

final readonly class MigrationHistoryStatus
{
    private const APPLIED = 'applied';
    private const ROLLED_BACK = 'rolled_back';

    private function __construct(private string $value)
    {
    }

    public static function applied(): self
    {
        return new self(self::APPLIED);
    }

    public static function rolledBack(): self
    {
        return new self(self::ROLLED_BACK);
    }

    public static function from(string $value): self
    {
        return match ($value) {
            self::APPLIED => self::applied(),
            self::ROLLED_BACK => self::rolledBack(),
            default => throw new InvalidMigrationHistoryStatusException(
                sprintf('Unsupported migration history status "%s".', $value),
            ),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
