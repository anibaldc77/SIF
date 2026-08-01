<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Transaction;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoTransactionPolicyException;

enum PdoExternalTransactionPolicy: string
{
    case Reject = 'reject';
    case Savepoint = 'savepoint';

    public static function fromValue(string $value): self
    {
        $normalized = strtolower(trim($value));

        return self::tryFrom($normalized)
            ?? throw new InvalidPdoTransactionPolicyException('Unsupported external transaction policy.');
    }
}
