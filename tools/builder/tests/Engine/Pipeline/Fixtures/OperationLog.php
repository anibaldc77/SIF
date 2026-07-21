<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Pipeline\Fixtures;

final class OperationLog
{
    /** @var list<string> */
    private array $operations = [];

    public function add(string $operation): void
    {
        $this->operations[] = $operation;
    }

    /** @return list<string> */
    public function all(): array
    {
        return $this->operations;
    }
}
