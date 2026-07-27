<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\TransactionState;

interface TransactionManagerInterface
{
    /**
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     */
    public function transactional(callable $operation): mixed;

    public function state(): TransactionState;

    public function depth(): int;
}
