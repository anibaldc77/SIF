<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Sql;

use PDO;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoSqlParameterException;

final readonly class PdoSqlParameter
{
    private string $name;
    private int $type;

    public function __construct(string $name, private mixed $value, ?int $type = null)
    {
        $normalized = ltrim(trim($name), ':');
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $normalized) !== 1) {
            throw new InvalidPdoSqlParameterException('SQL parameter name must be a canonical placeholder name.');
        }
        $resolvedType = $type ?? self::inferType($this->value);
        if (!in_array($resolvedType, [PDO::PARAM_NULL, PDO::PARAM_BOOL, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_LOB], true)) {
            throw new InvalidPdoSqlParameterException('Unsupported PDO parameter type.');
        }
        $this->name = $normalized;
        $this->type = $resolvedType;
    }

    public function name(): string { return $this->name; }
    public function placeholder(): string { return ':' . $this->name; }
    public function value(): mixed { return $this->value; }
    public function type(): int { return $this->type; }

    /** @return array{name: string, placeholder: string, type: int} */
    public function summary(): array
    {
        return ['name' => $this->name, 'placeholder' => $this->placeholder(), 'type' => $this->type];
    }

    private static function inferType(mixed $value): int
    {
        return match (true) {
            $value === null => PDO::PARAM_NULL,
            is_bool($value) => PDO::PARAM_BOOL,
            is_int($value) => PDO::PARAM_INT,
            is_string($value) => PDO::PARAM_STR,
            default => throw new InvalidPdoSqlParameterException('Parameter type must be explicit for non-scalar values.'),
        };
    }
}
