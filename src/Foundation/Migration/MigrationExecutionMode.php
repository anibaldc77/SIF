<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationExecutionModeException;

final readonly class MigrationExecutionMode
{
    private const DRY_RUN = 'dry-run';
    private const APPLY = 'apply';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!in_array($value, [self::DRY_RUN, self::APPLY], true)) {
            throw new InvalidMigrationExecutionModeException(
                'Migration execution mode must be "dry-run" or "apply".',
            );
        }

        $this->value = $value;
    }

    public static function dryRun(): self
    {
        return new self(self::DRY_RUN);
    }

    public static function apply(): self
    {
        return new self(self::APPLY);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function mutatesState(): bool
    {
        return $this->value === self::APPLY;
    }
}
