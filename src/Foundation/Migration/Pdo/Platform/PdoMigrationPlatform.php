<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Platform;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationPlatformException;

final readonly class PdoMigrationPlatform
{
    private const POSTGRESQL = 'postgresql';
    private const MYSQL = 'mysql';
    private const SQLSERVER = 'sqlserver';

    private string $value;

    public function __construct(string $value)
    {
        $value = self::normalize($value);

        if (!in_array($value, [self::POSTGRESQL, self::MYSQL, self::SQLSERVER], true)) {
            throw new InvalidPdoMigrationPlatformException(
                'PDO migration platform must be postgresql, mysql or sqlserver.',
            );
        }

        $this->value = $value;
    }

    public static function postgresql(): self
    {
        return new self(self::POSTGRESQL);
    }

    public static function mysql(): self
    {
        return new self(self::MYSQL);
    }

    public static function sqlserver(): self
    {
        return new self(self::SQLSERVER);
    }

    public static function fromDriver(string $driver): self
    {
        return new self($driver);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function driver(): string
    {
        return match ($this->value) {
            self::POSTGRESQL => 'pgsql',
            self::MYSQL => 'mysql',
            self::SQLSERVER => 'sqlsrv',
            default => throw new InvalidPdoMigrationPlatformException('Unsupported PDO migration platform state.'),
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function normalize(string $value): string
    {
        return match (strtolower(trim($value))) {
            'pgsql', 'postgres', 'postgresql' => self::POSTGRESQL,
            'mysql' => self::MYSQL,
            'sqlsrv', 'mssql', 'sqlserver' => self::SQLSERVER,
            default => strtolower(trim($value)),
        };
    }
}
