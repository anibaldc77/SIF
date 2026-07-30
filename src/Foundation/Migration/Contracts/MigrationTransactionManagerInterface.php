<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Contracts;

interface MigrationTransactionManagerInterface
{
    public function supportsTransactions(): bool;

    public function begin(): void;

    public function commit(): void;

    public function rollBack(): void;
}
