<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationIdException;

final readonly class MigrationId
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $value) !== 1) {
            throw new InvalidMigrationIdException(
                'Migration identifier must use only letters, numbers, dots, underscores, colons and hyphens.',
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
