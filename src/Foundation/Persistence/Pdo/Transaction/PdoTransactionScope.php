<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Transaction;

enum PdoTransactionScope: string
{
    case None = 'none';
    case Owned = 'owned';
    case Savepoint = 'savepoint';
}
