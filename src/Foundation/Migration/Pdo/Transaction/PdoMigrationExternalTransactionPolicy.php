<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Transaction;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationTransactionPolicyException;

final readonly class PdoMigrationExternalTransactionPolicy
{
    private const REJECT = 'reject';
    private const SAVEPOINT = 'savepoint';

    public function __construct(private string $value = self::REJECT)
    {
        if (!in_array($this->value, [self::REJECT, self::SAVEPOINT], true)) {
            throw new InvalidPdoMigrationTransactionPolicyException(
                'External transaction policy must be reject or savepoint.',
            );
        }
    }

    public static function reject(): self
    {
        return new self(self::REJECT);
    }

    public static function savepoint(): self
    {
        return new self(self::SAVEPOINT);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function rejectsExternalTransaction(): bool
    {
        return $this->value === self::REJECT;
    }

    public function usesSavepoint(): bool
    {
        return $this->value === self::SAVEPOINT;
    }
}
