<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationVersionException;

final readonly class MigrationVersion
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._+:-]*$/D', $value) !== 1) {
            throw new InvalidMigrationVersionException(
                'Migration version must be a non-empty safe version token.',
            );
        }

        $this->value = $value;
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
