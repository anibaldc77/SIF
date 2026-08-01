<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Connection;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoPersistenceConnectionOwnershipException;

final readonly class PdoPersistenceConnectionOwnership
{
    private const INTERNAL = 'internal';
    private const EXTERNAL = 'external';
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, [self::INTERNAL, self::EXTERNAL], true)) {
            throw new InvalidPdoPersistenceConnectionOwnershipException('Connection ownership must be internal or external.');
        }
        $this->value = $normalized;
    }

    public static function internal(): self { return new self(self::INTERNAL); }
    public static function external(): self { return new self(self::EXTERNAL); }
    public function value(): string { return $this->value; }
    public function externallyOwned(): bool { return $this->value === self::EXTERNAL; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
