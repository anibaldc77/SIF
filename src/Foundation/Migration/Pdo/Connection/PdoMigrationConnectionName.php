<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Connection;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationConnectionNameException;

final readonly class PdoMigrationConnectionName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $value) !== 1) {
            throw new InvalidPdoMigrationConnectionNameException(
                'PDO migration connection name must use only letters, numbers, dots, underscores, colons and hyphens.',
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
