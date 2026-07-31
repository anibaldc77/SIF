<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Connection;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationConnectionOwnershipException;

final readonly class PdoMigrationConnectionOwnership
{
    private const EXTERNAL = 'external';
    private const ADAPTER = 'adapter';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!in_array($value, [self::EXTERNAL, self::ADAPTER], true)) {
            throw new InvalidPdoMigrationConnectionOwnershipException(
                'PDO migration connection ownership must be external or adapter.',
            );
        }

        $this->value = $value;
    }

    public static function external(): self
    {
        return new self(self::EXTERNAL);
    }

    public static function adapter(): self
    {
        return new self(self::ADAPTER);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function externallyOwned(): bool
    {
        return $this->value === self::EXTERNAL;
    }

    public function adapterOwned(): bool
    {
        return $this->value === self::ADAPTER;
    }
}
