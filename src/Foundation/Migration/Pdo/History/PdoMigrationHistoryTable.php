<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\History;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationHistoryTableException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final readonly class PdoMigrationHistoryTable
{
    private string $name;

    private ?string $schema;

    public function __construct(string $name = 'sif_migration_history', ?string $schema = null)
    {
        $name = trim($name);
        $schema = $schema === null ? null : trim($schema);

        if (!self::validIdentifier($name)) {
            throw new InvalidPdoMigrationHistoryTableException(
                'PDO migration history table name must be a safe SQL identifier.',
            );
        }

        if ($schema !== null && !self::validIdentifier($schema)) {
            throw new InvalidPdoMigrationHistoryTableException(
                'PDO migration history schema must be a safe SQL identifier.',
            );
        }

        $this->name = $name;
        $this->schema = $schema;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function schema(): ?string
    {
        return $this->schema;
    }

    public function qualified(PdoMigrationPlatform $platform): string
    {
        $name = $this->quote($this->name, $platform);

        if ($this->schema === null) {
            return $name;
        }

        return $this->quote($this->schema, $platform) . '.' . $name;
    }

    public function logicalName(): string
    {
        return $this->schema === null ? $this->name : $this->schema . '.' . $this->name;
    }

    private function quote(string $identifier, PdoMigrationPlatform $platform): string
    {
        return match ($platform->value()) {
            'postgresql' => '"' . $identifier . '"',
            'mysql' => '`' . $identifier . '`',
            'sqlserver' => '[' . $identifier . ']',
            default => throw new InvalidPdoMigrationHistoryTableException(
                'Unsupported PDO migration platform for history table quoting.',
            ),
        };
    }

    private static function validIdentifier(string $value): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $value) === 1;
    }
}
